/*
  CurseForge Downloads Action - Badge & Chart Generator Script
  ------------------------------------------------------------------
  Load this file in a Joomla Custom HTML module or template:
    <script src="/path/to/cfdg-script.js"></script>
*/
(function () {
  "use strict";

  var CFDG = document.getElementById("cfdg-root");
  if (!CFDG) return;

  var SAFE_SLUG = /^[A-Za-z0-9_.-]+$/;
  var SAFE_PATH = /^[A-Za-z0-9_.\-\/]+$/;
  var SAFE_COLOR = /^[0-9A-Fa-f]{6}$/;

  function el(id) { return document.getElementById(id); }

  function value(id, fallback) {
    var node = el(id);
    return node && typeof node.value === "string" ? node.value.trim() : fallback;
  }

  function sanitizeSlug(text, fallback) {
    return SAFE_SLUG.test(text) ? text : fallback;
  }

  function sanitizePath(text, fallback) {
    var normalized = text.replace(/\\/g, "/").replace(/^\/+/, "");
    if (normalized.indexOf("..") !== -1) return fallback;
    return SAFE_PATH.test(normalized) ? normalized : fallback;
  }

  function sanitizeColor(text, fallback) {
    return SAFE_COLOR.test(text) ? text.toUpperCase() : fallback;
  }

  function encode(text) { return encodeURIComponent(text); }

  function rawBase(owner, repo, branch) {
    return "https://raw.githubusercontent.com/" + owner + "/" + repo + "/" + branch;
  }

  function badgeUrl(owner, repo, branch, jsonPath, query, label, color) {
    var source = rawBase(owner, repo, branch) + "/" + jsonPath;
    return "https://img.shields.io/badge/dynamic/json?url=" + encode(source) +
      "&query=" + encode(query) +
      "&label=" + encode(label) +
      "&color=" + color +
      "&style=flat-square&logo=curseforge&cacheSeconds=900";
  }

  function chartUrl(owner, repo, branch, path) {
    return rawBase(owner, repo, branch) + "/" + path;
  }

  function hidePlaceholder(imgEl) {
    var placeholder = imgEl.previousElementSibling;
    if (placeholder && placeholder.classList.contains("cfdg-preview-placeholder")) {
      placeholder.style.display = "none";
    }
  }

  function showPlaceholder(imgEl) {
    var placeholder = imgEl.previousElementSibling;
    if (placeholder && placeholder.classList.contains("cfdg-preview-placeholder")) {
      placeholder.style.display = "flex";
    }
    imgEl.style.display = "none";
  }

  function render() {
    var owner = sanitizeSlug(value("cfdg-owner", ""), "OWNER");
    var repo = sanitizeSlug(value("cfdg-repo", ""), "REPOSITORY");
    var branch = sanitizeSlug(value("cfdg-branch", ""), "stats-data");
    var jsonPath = sanitizePath(value("cfdg-jsonpath", ""), "curseforge/downloads.json");
    var primaryPath = sanitizePath(value("cfdg-chartpath", ""), "curseforge/downloads-trend.svg");
    var chartDir = sanitizePath(value("cfdg-chartdir", ""), "curseforge/charts");

    var metricParts = value("cfdg-metric", "total|$.stats.total|0A7EA4").split("|");
    var defaultQuery = metricParts[1] || "$.stats.total";
    var defaultColor = metricParts[2] || "0A7EA4";
    var query = value("cfdg-label", "") ? defaultQuery : defaultQuery;
    var label = value("cfdg-label", "downloads/total") || ("downloads/" + metricParts[0]);
    var color = sanitizeColor(value("cfdg-color", ""), defaultColor);

    var badge = badgeUrl(owner, repo, branch, jsonPath, query, label, color);

    var chartType = value("cfdg-charttype", "total-trend");
    var theme = value("cfdg-theme", "slate");
    var usePrimary = el("cfdg-primary") && el("cfdg-primary").checked;
    var chartTarget = usePrimary
      ? primaryPath
      : chartDir + "/" + chartType + "--" + theme + ".svg";
    var chart = chartUrl(owner, repo, branch, chartTarget);

    var badgeMd = "![" + label + "](" + badge + ")";
    var chartMd = "![Downloads " + chartType + " " + theme + "](" + chart + ")";

    el("cfdg-badge-md").textContent = badgeMd;
    el("cfdg-badge-url").textContent = badge;

    var badgeImg = el("cfdg-badge-img");
    badgeImg.style.display = "";
    hidePlaceholder(badgeImg);
    badgeImg.src = badge;

    el("cfdg-chart-md").textContent = chartMd;
    el("cfdg-chart-url").textContent = chart;

    var chartImg = el("cfdg-chart-img");
    chartImg.style.display = "";
    hidePlaceholder(chartImg);
    chartImg.src = chart;
  }

  function setStatus(message, isError) {
    var node = el("cfdg-status");
    if (!node) return;
    node.textContent = message;
    node.className = "cfdg-status mb-0" + (isError ? " cfdg-error" : "");
    if (message) {
      window.setTimeout(function () {
        if (node.textContent === message) {
          node.textContent = "";
          node.className = "cfdg-status mb-0";
        }
      }, 2500);
    }
  }

  function copyFrom(sourceId) {
    var source = el(sourceId);
    if (!source) return;
    var text = source.textContent || "";

    function done(ok) { setStatus(ok ? "Copied to clipboard." : "Copy failed; select and copy manually.", !ok); }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () { done(true); }, function () { fallbackCopy(text, done); });
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

  CFDG.addEventListener("input", render);
  CFDG.addEventListener("change", render);

  var copyButtons = CFDG.querySelectorAll("[data-copy]");
  for (var i = 0; i < copyButtons.length; i += 1) {
    copyButtons[i].addEventListener("click", function () {
      copyFrom(this.getAttribute("data-copy"));
    });
  }

  // Set up image load/error handlers for placeholders
  var badgeImg = el("cfdg-badge-img");
  if (badgeImg) {
    badgeImg.addEventListener("load", function () { hidePlaceholder(this); });
    badgeImg.addEventListener("error", function () { showPlaceholder(this); });
  }

  var chartImg = el("cfdg-chart-img");
  if (chartImg) {
    chartImg.addEventListener("load", function () { hidePlaceholder(this); });
    chartImg.addEventListener("error", function () { showPlaceholder(this); });
  }

  render();
}());
