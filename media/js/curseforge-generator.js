/*
  CurseForge Downloads Action - Badge Generator Script
  ------------------------------------------------------------------
  AJAX-based generator for dynamic badge preview and output.
  Badge URL construction is handled server-side.
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

  // ---- Color picker sync ----
  function syncColorPicker(pickerId, textId) {
    var picker = el(pickerId);
    var text = el(textId);
    if (!picker || !text) return;

    picker.addEventListener("input", function () {
      text.value = picker.value.replace("#", "").toUpperCase();
      scheduleGenerate();
    });

    text.addEventListener("input", function () {
      var hex = text.value.replace("#", "").toUpperCase();
      text.value = hex;
      if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
        picker.value = "#" + hex;
      }
      scheduleGenerate();
    });
  }

  syncColorPicker("curseforge-color-picker", "curseforge-color");

  // ---- Metric color auto-update ----
  var metricSelect = el("curseforge-metric");
  if (metricSelect) {
    metricSelect.addEventListener("change", function () {
      var val = metricSelect.value || "";
      var parts = val.split("|");
      if (parts.length >= 3 && /^[0-9A-Fa-f]{6}$/.test(parts[2])) {
        var colorText = el("curseforge-color");
        var colorPicker = el("curseforge-color-picker");
        if (colorText) {
          colorText.value = parts[2].toUpperCase();
        }
        if (colorPicker) {
          colorPicker.value = "#" + parts[2].toUpperCase();
        }
      }
      if (parts.length >= 1) {
        var labelInput = el("curseforge-label");
        if (labelInput && (!labelInput.dataset.userEdited || labelInput.dataset.userEdited === "false")) {
          var metricName = parts[0];
          labelInput.value = "downloads/" + metricName;
        }
      }
      scheduleGenerate();
    });
  }

  // Track if label was manually edited
  var labelInputTrack = el("curseforge-label");
  if (labelInputTrack) {
    labelInputTrack.addEventListener("input", function () {
      labelInputTrack.dataset.userEdited = "true";
    });
  }

  // ---- Tab switching ----
  var tabs = curseforge.querySelectorAll(".nav-tab-btn");
  var panels = {
    badge: el("curseforge-panel-badge"),
    chart: el("curseforge-panel-chart")
  };

  function switchTab(target) {
    // Update tab buttons
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].classList.remove("active", "bg-primary");
      tabs[i].classList.add("bg-info");
      tabs[i].setAttribute("aria-selected", "false");
    }
    // Update panels
    for (var key in panels) {
      if (panels.hasOwnProperty(key)) {
        panels[key].classList.remove("show");
        panels[key].classList.add("d-none");
      }
    }
    // Activate target
    for (var j = 0; j < tabs.length; j++) {
      if (tabs[j].getAttribute("data-tab") === target) {
        tabs[j].classList.add("active", "bg-primary");
        tabs[j].classList.remove("bg-info");
        tabs[j].setAttribute("aria-selected", "true");
        break;
      }
    }
    if (panels[target]) {
      panels[target].classList.add("show");
      panels[target].classList.remove("d-none");
    }
    // Re-trigger generation
    scheduleGenerate();
  }

  for (var t = 0; t < tabs.length; t++) {
    tabs[t].addEventListener("click", (function (tab) {
      return function () {
        switchTab(tab.getAttribute("data-tab"));
      };
    })(tabs[t]));
  }

  // ---- Apply URL parameters to pre-fill form ----
  if (config.urlMetric) {
    var metricSelect = el("curseforge-metric");
    if (metricSelect) {
      for (var m = 0; m < metricSelect.options.length; m++) {
        var parts = metricSelect.options[m].value.split("|");
        if (parts[0] === config.urlMetric) {
          metricSelect.selectedIndex = m;
          break;
        }
      }
    }
  }

  if (config.urlColor) {
    var colorText = el("curseforge-color");
    var colorPicker = el("curseforge-color-picker");
    var cleanColor = config.urlColor.replace("#", "");
    if (/^[0-9A-Fa-f]{6}$/.test(cleanColor)) {
      if (colorText) colorText.value = cleanColor;
      if (colorPicker) colorPicker.value = "#" + cleanColor;
    }
  }

  if (config.urlLabel) {
    var labelInput = el("curseforge-label");
    if (labelInput) {
      labelInput.value = decodeURIComponent(config.urlLabel);
    }
  }

  // Switch to URL-specified tab
  if (config.urlTab && panels[config.urlTab]) {
    switchTab(config.urlTab);
  }

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
      setStatus(Joomla.Text._("MOD_curseforge_GENERATOR_AJAX_ERROR") || "AJAX endpoint not configured.", true);
      return;
    }

    var formData = new FormData();
    formData.append("owner", value("curseforge-owner", ""));
    formData.append("repo", value("curseforge-repo", ""));
    formData.append("branch", value("curseforge-branch", ""));
    formData.append("jsonpath", "curseforge/downloads.json");
    formData.append("metric", value("curseforge-metric", "total|$.stats.total|0A7EA4"));
    formData.append("label", value("curseforge-label", "downloads/total"));
    formData.append("color", value("curseforge-color", "0A7EA4"));
    formData.append("style", value("curseforge-style", "flat-square"));
    formData.append("provider", "raw");
    formData.append("labelcolor", value("curseforge-label-color", "555555"));
    formData.append("logo", "");
    formData.append("cache", "0");
    formData.append("charttype", value("curseforge-charttype", "total-trend"));
    formData.append("theme", value("curseforge-theme", "slate"));
    formData.append("chartdata", value("curseforge-chartdata", ""));
    // CSRF token
    var csrfToken = Joomla.getOptions("system.csrf.token") || Joomla.getOptions("csrf.token");
    if (csrfToken) {
      formData.append(csrfToken.name, csrfToken.value);
    }

    // Show loading state
    setStatus("");

    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin"
    })
    .then(function (response) {
      return response.text().then(function (text) {
        return { status: response.status, text: text };
      });
    })
    .then(function (result) {
      var text = result.text;
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
          throw new Error("Invalid JSON response (HTTP " + result.status + "): " + text.substring(0, 200));
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
      var newUrl = badge.url + (badge.url.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      img.src = newUrl;
      showPreview(content, placeholder, true);
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
      showPreview(content, placeholder, true);
    }
  }

  function setPreviewStatus(node) {
    if (node && node.getAttribute("data-error-text")) {
      node.textContent = node.getAttribute("data-error-text");
    }
  }

  function showPreview(content, placeholder, show) {
    if (show) {
      content.classList.remove("d-none");
      placeholder.classList.add("d-none");
    } else {
      content.classList.add("d-none");
      placeholder.classList.remove("d-none");
    }
  }

  function setStatus(message, isError) {
    var successNode = el("curseforge-status-success");
    var errorNode = el("curseforge-status-error");

    // Clear both alerts
    if (successNode) {
      successNode.textContent = "";
      successNode.classList.add("d-none");
    }
    if (errorNode) {
      errorNode.textContent = "";
      errorNode.classList.add("d-none");
    }

    if (!message) return;

    var node = isError ? errorNode : successNode;
    if (!node) return;

    node.textContent = message;
    node.classList.remove("d-none");

    window.setTimeout(function () {
      if (node.textContent === message) {
        node.textContent = "";
        node.classList.add("d-none");
      }
    }, 2500);
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
      if (content && placeholder) showPreview(content, placeholder, true);
    });
    badgeImg.addEventListener("error", function () {
      var content = el("curseforge-badge-content");
      var placeholder = el("curseforge-badge-placeholder");
      setPreviewStatus(el("curseforge-badge-status"));
      if (content && placeholder) showPreview(content, placeholder, false);
    });
  }

  var chartImg = el("curseforge-chart-img");
  if (chartImg) {
    chartImg.addEventListener("load", function () {
      var content = el("curseforge-chart-content");
      var placeholder = el("curseforge-chart-placeholder");
      if (content && placeholder) showPreview(content, placeholder, true);
    });
    chartImg.addEventListener("error", function () {
      var content = el("curseforge-chart-content");
      var placeholder = el("curseforge-chart-placeholder");
      setPreviewStatus(el("curseforge-chart-status"));
      if (content && placeholder) showPreview(content, placeholder, false);
    });
  }

  // ---- Bind input/change events ----
  curseforge.addEventListener("input", scheduleGenerate);
  curseforge.addEventListener("change", scheduleGenerate);

  // Initial generation
  scheduleGenerate();
}());
