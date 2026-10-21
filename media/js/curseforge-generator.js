/*
  CurseForge Downloads Action - Badge & Chart Generator Script
  ------------------------------------------------------------------
  AJAX-based generator matching the GDA Generator Lab UI.
  All badge/chart URL construction is handled server-side.
*/
(function () {
  "use strict";

  var curseforge = document.getElementById("curseforge-root");
  if (!curseforge) return;

  // Read config from Joomla script options
  var config = Joomla.getOptions("mod_curseforge_generator") || {};
  var ajaxUrl = config.ajaxUrl || "";

  function el(id) { return document.getElementById(id); }

  function value(id, fallback) {
    var node = el(id);
    return node && typeof node.value === "string" ? node.value.trim() : fallback;
  }

  // ---- Tab switching ----
  var tabs = curseforge.querySelectorAll(".curseforge-tab");
  var panels = {
    basic: el("curseforge-panel-basic"),
    advanced: el("curseforge-panel-advanced")
  };

  for (var t = 0; t < tabs.length; t++) {
    tabs[t].addEventListener("click", (function (tab) {
      return function () {
        var target = tab.getAttribute("data-tab");
        // Update tab buttons
        for (var i = 0; i < tabs.length; i++) {
          tabs[i].classList.remove("curseforge-tab-active");
          tabs[i].setAttribute("aria-selected", "false");
        }
        tab.classList.add("curseforge-tab-active");
        tab.setAttribute("aria-selected", "true");
        // Update panels
        for (var key in panels) {
          if (panels.hasOwnProperty(key)) {
            panels[key].classList.remove("curseforge-panel-active");
          }
        }
        if (panels[target]) {
          panels[target].classList.add("curseforge-panel-active");
        }
        // Re-trigger generation in case advanced fields changed
        scheduleGenerate();
      };
    })(tabs[t]));
  }

  // ---- Color picker sync ----
  function syncColorPicker(pickerId, textId) {
    var picker = el(pickerId);
    var text = el(textId);
    if (!picker || !text) return;

    picker.addEventListener("input", function () {
      text.value = picker.value.replace("#", "");
      scheduleGenerate();
    });

    text.addEventListener("input", function () {
      var hex = text.value.replace("#", "");
      if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
        picker.value = "#" + hex;
      }
      scheduleGenerate();
    });
  }

  syncColorPicker("curseforge-color-picker", "curseforge-color");
  syncColorPicker("curseforge-chart-color-picker", "curseforge-chart-color");

  // ---- Debounced AJAX generation ----
  var generateTimer = null;

  function scheduleGenerate() {
    if (generateTimer) {
      clearTimeout(generateTimer);
    }
    generateTimer = setTimeout(doGenerate, 300);
  }

  function doGenerate() {
    if (!ajaxUrl) {
      setStatus(Joomla.JText._("MOD_curseforge_GENERATOR_AJAX_ERROR") || "AJAX endpoint not configured.", true);
      return;
    }

    var formData = new FormData();
    formData.append("owner", value("curseforge-owner", ""));
    formData.append("repo", value("curseforge-repo", ""));
    formData.append("branch", value("curseforge-branch", ""));
    formData.append("jsonpath", value("curseforge-jsonpath", "curseforge/downloads.json"));
    formData.append("chartpath", value("curseforge-chartpath", "curseforge/downloads-trend.svg"));
    formData.append("chartdir", value("curseforge-chartdir", "curseforge/charts"));
    formData.append("metric", value("curseforge-metric", "total|$.stats.total|0A7EA4"));
    formData.append("label", value("curseforge-label", "downloads/total"));
    formData.append("color", value("curseforge-color", "0A7EA4"));
    formData.append("charttype", value("curseforge-charttype", "total-trend"));
    formData.append("theme", value("curseforge-theme", "slate"));
    formData.append("primary", el("curseforge-primary") && el("curseforge-primary").checked ? "1" : "0");
    formData.append("chart-color", value("curseforge-chart-color", "0A7EA4"));
    // CSRF token
    var csrfToken = Joomla.getOptions("csrf.token");
    if (csrfToken) {
      formData.append(csrfToken, "1");
    }

    // Show loading state
    setStatus("");

    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin"
    })
    .then(function (response) {
      return response.text();
    })
    .then(function (text) {
      // Joomla wraps JSON in the message queue; try to parse
      var data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        // If the response is wrapped, try to extract JSON
        var match = text.match(/\{[\s\S]*\}/);
        if (match) {
          data = JSON.parse(match[0]);
        } else {
          throw new Error("Invalid JSON response");
        }
      }

      if (data.success) {
        updateBadge(data.badge);
        updateChart(data.chart);
        setStatus("");
      } else {
        setStatus(data.message || "Generation failed.", true);
      }
    })
    .catch(function (err) {
      setStatus("Request failed: " + err.message, true);
    });
  }

  function updateBadge(badge) {
    if (!badge || !badge.url) return;

    el("curseforge-badge-url").textContent = badge.url;
    el("curseforge-badge-md").textContent = badge.md || "";

    var content = el("curseforge-badge-content");
    var placeholder = el("curseforge-badge-placeholder");
    var img = el("curseforge-badge-img");

    if (content && placeholder && img) {
      img.src = badge.url;
      content.style.display = "block";
      placeholder.style.display = "none";
    }
  }

  function updateChart(chart) {
    if (!chart || !chart.url) return;

    el("curseforge-chart-url").textContent = chart.url;
    el("curseforge-chart-md").textContent = chart.md || "";

    var content = el("curseforge-chart-content");
    var placeholder = el("curseforge-chart-placeholder");
    var img = el("curseforge-chart-img");

    if (content && placeholder && img) {
      img.src = chart.url;
      content.style.display = "block";
      placeholder.style.display = "none";
    }
  }

  function setStatus(message, isError) {
    var node = el("curseforge-status");
    if (!node) return;
    node.textContent = message;
    node.className = "curseforge-status" + (isError ? " curseforge-error" : "");
    if (message) {
      window.setTimeout(function () {
        if (node.textContent === message) {
          node.textContent = "";
          node.className = "curseforge-status";
        }
      }, 2500);
    }
  }

  // ---- Copy buttons ----
  function copyFrom(sourceId) {
    var source = el(sourceId);
    if (!source) return;
    var text = source.textContent || "";

    function done(ok) {
      setStatus(ok ? "Copied to clipboard." : "Copy failed; select and copy manually.", !ok);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(
        function () { done(true); },
        function () { fallbackCopy(text, done); }
      );
    } else {
      fallbackCopy(text, done);
    }
  }

  function fallbackCopy(text, done) {
    try {
      var area = document.createElement("textarea");
      area.value = text;
      area.setAttribute("readonly", "");
      area.style.position = "fixed";
      area.style.top = "-1000px";
      document.body.appendChild(area);
      area.select();
      var ok = document.execCommand("copy");
      document.body.removeChild(area);
      done(ok);
    } catch (error) {
      done(false);
    }
  }

  var copyButtons = curseforge.querySelectorAll("[data-copy]");
  for (var i = 0; i < copyButtons.length; i += 1) {
    copyButtons[i].addEventListener("click", function () {
      copyFrom(this.getAttribute("data-copy"));
    });
  }

  // ---- Image load/error handlers ----
  var badgeImg = el("curseforge-badge-img");
  if (badgeImg) {
    badgeImg.addEventListener("load", function () {
      var content = el("curseforge-badge-content");
      var placeholder = el("curseforge-badge-placeholder");
      if (content) content.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
    });
    badgeImg.addEventListener("error", function () {
      var content = el("curseforge-badge-content");
      var placeholder = el("curseforge-badge-placeholder");
      if (content) content.style.display = "none";
      if (placeholder) placeholder.style.display = "flex";
    });
  }

  var chartImg = el("curseforge-chart-img");
  if (chartImg) {
    chartImg.addEventListener("load", function () {
      var content = el("curseforge-chart-content");
      var placeholder = el("curseforge-chart-placeholder");
      if (content) content.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
    });
    chartImg.addEventListener("error", function () {
      var content = el("curseforge-chart-content");
      var placeholder = el("curseforge-chart-placeholder");
      if (content) content.style.display = "none";
      if (placeholder) placeholder.style.display = "flex";
    });
  }

  // ---- Bind input/change events ----
  curseforge.addEventListener("input", scheduleGenerate);
  curseforge.addEventListener("change", scheduleGenerate);

  // Initial generation
  scheduleGenerate();
}());
