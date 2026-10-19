/* Visual layer only: animations and nicer layouts for what the PHP already prints.
   Nothing here changes form fields, links or what gets sent to the server.
   If this file fails to load, every page still works with the plain styling. */
(function () {
  "use strict";

  var root = document.documentElement;
  root.classList.add("js");

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var finePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  var ICONS = {
    confirm: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
    accept: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
    decline: '<path d="M7 7l10 10M17 7 7 17"/>',
    cancel: '<path d="M7 7l10 10M17 7 7 17"/>',
    edit: '<path d="M4.5 19.5h4l10-10a2.1 2.1 0 0 0-3-3l-10 10Z"/><path d="m13.5 7.5 3 3"/>',
    "delete": '<path d="M4.5 7h15M9.5 7V5h5v2M6.5 7l1 12.5h9l1-12.5"/>'
  };

  function svg(paths) {
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' + paths + "</svg>";
  }

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  ready(function () {
    markActiveLink();
    pageIntro();
    enhanceRecords();
    countSections();
    enhanceSlots();
    setupReveal();
    setupPointerEffects();
    setupButtons();
    setupMiniBoard();
    setupConfirm();
  });

  /* ---------- confirm page: focus starts on No, Esc goes back ---------- */
  function setupConfirm() {
    var no = document.querySelector(".confirm-no");
    if (!no) return;
    no.focus({ preventScroll: true });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") location.href = no.href;
    });
  }

  /* ---------- the current page is highlighted in the menu ---------- */
  function markActiveLink() {
    var here = location.pathname.split("/").pop() || "index.php";
    if (here === "edit_appointment.php") here = "staff_appointments.php";
    var links = document.querySelectorAll(".nav-links a");
    for (var i = 0; i < links.length; i++) {
      var label = links[i].querySelector("span");
      if (label) links[i].setAttribute("data-label", label.textContent.trim());
      var target = links[i].pathname.split("/").pop();
      if (target === here && links[i].search.indexOf("logout") === -1) {
        links[i].classList.add("active");
        links[i].setAttribute("aria-current", "page");
        if (links[i].scrollIntoView && window.innerWidth < 1100) {
          links[i].scrollIntoView({ block: "nearest", inline: "center" });
        }
      }
    }
  }

  /* ---------- small date line above the page title + tab title ---------- */
  function pageIntro() {
    var h2 = document.querySelector(".content > h2");
    var anyH2 = h2 || document.querySelector(".content h2");
    if (anyH2) document.title = anyH2.textContent.trim() + " · Appointment System";
    if (!h2) return;

    var eyebrow = document.createElement("p");
    eyebrow.className = "eyebrow";
    try {
      eyebrow.textContent = new Date().toLocaleDateString(undefined, {
        weekday: "long", day: "numeric", month: "long", year: "numeric"
      });
    } catch (e) {
      eyebrow.textContent = new Date().toDateString();
    }
    h2.parentNode.insertBefore(eyebrow, h2);
  }

  /* ---------- records: "ID: 5 | Name: Ana | Status: ..." become labeled fields ---------- */
  function enhanceRecords() {
    var records = document.querySelectorAll(".record");
    for (var i = 0; i < records.length; i++) {
      try {
        enhanceRecord(records[i]);
      } catch (e) {
        /* leave this record as plain text if anything unexpected is inside */
      }
    }
  }

  function enhanceRecord(rec) {
    var links = [];
    var kids = Array.prototype.slice.call(rec.childNodes);
    var segments = [[]];

    kids.forEach(function (node) {
      if (node.nodeType === 1 && node.tagName === "A") {
        links.push(node);
        return;
      }
      if (node.nodeType === 3) {
        var parts = node.textContent.split(" | ");
        parts.forEach(function (part, idx) {
          if (idx > 0) segments.push([]);
          if (part.trim() !== "") segments[segments.length - 1].push(document.createTextNode(part));
        });
        return;
      }
      segments[segments.length - 1].push(node);
    });

    var fields = document.createElement("div");
    fields.className = "record-fields";

    segments.forEach(function (nodes) {
      if (!nodes.length) return;

      var label = "";
      var first = nodes[0];
      if (first.nodeType === 3) {
        var m = first.textContent.match(/^\s*([A-Za-z][A-Za-z ]{0,23}):\s*/);
        if (m) {
          label = m[1].trim();
          first.textContent = first.textContent.slice(m[0].length);
        }
      }

      var field = document.createElement("div");
      field.className = "rf";
      var key = label.toLowerCase();
      if (key === "id") field.classList.add("is-id");
      else if (key === "date") field.classList.add("is-date");
      else if (key === "time") field.classList.add("is-time");
      else if (key === "contact") field.classList.add("is-contact");
      else if (key === "price") field.classList.add("is-price");
      else if (key === "status") field.classList.add("is-status");
      else if (!label) field.classList.add("is-plain");

      if (label) {
        var l = document.createElement("span");
        l.className = "rf-label";
        l.textContent = label;
        field.appendChild(l);
      }

      var v = document.createElement("span");
      v.className = "rf-value";
      nodes.forEach(function (n) {
        if (n.nodeType === 3) n.textContent = n.textContent.replace(/^\s+|\s+$/g, "");
        v.appendChild(n);
      });

      /* notifications mark unread ones with "[NEW] " */
      if (!label && v.firstChild && v.firstChild.nodeType === 3 && v.firstChild.textContent.indexOf("[NEW]") === 0) {
        v.firstChild.textContent = v.firstChild.textContent.replace(/^\[NEW\]\s*/, "");
        rec.classList.add("is-new");
        var dot = document.createElement("span");
        dot.className = "new-dot";
        dot.setAttribute("aria-label", "New");
        rec.insertBefore(dot, rec.firstChild);
      }

      if (v.textContent.trim() === "" && !v.querySelector("*")) return;
      field.appendChild(v);
      fields.appendChild(field);
    });

    /* remove the old loose text, keep everything else in place */
    kids.forEach(function (n) {
      if (n.parentNode === rec && n.nodeType === 3) rec.removeChild(n);
    });
    rec.appendChild(fields);

    if (links.length) {
      var actions = document.createElement("div");
      actions.className = "record-actions";
      links.forEach(function (a) {
        var word = a.textContent.trim().toLowerCase();
        if (ICONS[word]) a.insertAdjacentHTML("afterbegin", svg(ICONS[word]));
        actions.appendChild(a);
      });
      rec.appendChild(actions);
    }
  }

  /* ---------- a small count next to each list heading ---------- */
  function countSections() {
    var heads = document.querySelectorAll(".content h3");
    for (var i = 0; i < heads.length; i++) {
      var n = 0;
      var hasList = false;
      var el = heads[i].nextElementSibling;
      while (el && el.tagName !== "H3" && el.tagName !== "SECTION") {
        if (el.classList.contains("record")) { n++; hasList = true; }
        if (el.classList.contains("empty")) hasList = true;
        el = el.nextElementSibling;
      }
      if (hasList) {
        var c = document.createElement("span");
        c.className = "count";
        c.textContent = n;
        heads[i].appendChild(c);
      }
    }
  }

  /* ---------- available times: "09:00 - Available" becomes a tile ---------- */
  function enhanceSlots() {
    var slots = document.querySelectorAll(".slot");
    for (var i = 0; i < slots.length; i++) {
      var text = slots[i].textContent;
      var parts = text.split(" - ");
      if (parts.length < 2) continue;
      slots[i].textContent = "";
      var t = document.createElement("span");
      t.className = "slot-time";
      t.textContent = parts[0].trim();
      var s = document.createElement("span");
      s.className = "slot-state";
      s.textContent = parts.slice(1).join(" - ").trim();
      slots[i].appendChild(t);
      slots[i].appendChild(s);
    }
  }

  /* ---------- staggered reveal as things come into view ---------- */
  function setupReveal() {
    var groups = [];
    var content = document.querySelector(".content");
    if (!content) return;

    function addGroup(list) {
      var items = Array.prototype.slice.call(list);
      if (items.length) groups.push(items);
    }

    /* top-level blocks of the page */
    addGroup(Array.prototype.filter.call(content.children, function (el) {
      return !el.classList.contains("split") && !el.classList.contains("auth") &&
        !el.classList.contains("confirm") &&
        !el.classList.contains("stats") && !el.classList.contains("slots");
    }));
    addGroup(content.querySelectorAll(".split > .panel"));
    addGroup(content.querySelectorAll(".auth-panel > *"));
    content.querySelectorAll(".list").forEach(function (list) {
      addGroup(list.children);
    });
    content.querySelectorAll(".stats").forEach(function (g) { addGroup(g.children); });
    content.querySelectorAll(".slots").forEach(function (g) { addGroup(g.children); });
    content.querySelectorAll(".profile").forEach(function (g) {
      addGroup(g.querySelectorAll(".profile-list > div"));
    });

    var all = [];
    groups.forEach(function (items) {
      items.forEach(function (el, idx) {
        if (el.classList.contains("reveal")) return;
        el.classList.add("reveal");
        el.style.setProperty("--i", Math.min(idx, 12));
        all.push(el);
      });
    });

    function show(el) {
      el.classList.add("in");
      if (el.classList.contains("stat")) countUp(el);
      var finish = function () { el.classList.add("done"); };
      setTimeout(finish, 1500);
    }

    if (reduceMotion || !("IntersectionObserver" in window)) {
      all.forEach(function (el) { el.classList.add("in", "done"); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          show(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: "0px 0px -6% 0px", threshold: 0.05 });

    all.forEach(function (el) { io.observe(el); });
  }

  /* ---------- dashboard numbers count up from 0 ---------- */
  function countUp(stat) {
    var b = stat.querySelector("b");
    if (!b || b.dataset.counted) return;
    var target = parseInt(b.textContent, 10);
    if (isNaN(target) || target === 0) return;
    b.dataset.counted = "1";
    b.style.minWidth = b.offsetWidth + "px";

    var start = null;
    var duration = Math.min(1400, 600 + target * 20);
    function tick(now) {
      if (start === null) start = now;
      var p = Math.min(1, (now - start) / duration);
      var eased = 1 - Math.pow(1 - p, 4);
      b.textContent = Math.round(target * eased);
      if (p < 1) requestAnimationFrame(tick);
      else b.textContent = target;
    }
    b.textContent = "0";
    requestAnimationFrame(tick);
  }

  /* ---------- light that follows the cursor on cards ---------- */
  function setupPointerEffects() {
    if (!finePointer || reduceMotion) return;
    document.addEventListener("pointermove", function (e) {
      var card = e.target.closest && e.target.closest(".record, .stat");
      if (!card) return;
      var r = card.getBoundingClientRect();
      card.style.setProperty("--mx", (e.clientX - r.left) + "px");
      card.style.setProperty("--my", (e.clientY - r.top) + "px");
    }, { passive: true });
  }

  /* ---------- buttons: slight pull toward the cursor, ripple, busy state ---------- */
  function setupButtons() {
    var buttons = document.querySelectorAll("button, input[type=submit]");

    for (var i = 0; i < buttons.length; i++) {
      (function (btn) {
        if (finePointer && !reduceMotion) {
          btn.addEventListener("pointermove", function (e) {
            var r = btn.getBoundingClientRect();
            var x = (e.clientX - r.left - r.width / 2) * 0.18;
            var y = (e.clientY - r.top - r.height / 2) * 0.3;
            btn.style.transform = "translate(" + x.toFixed(1) + "px," + y.toFixed(1) + "px)";
          });
          btn.addEventListener("pointerleave", function () {
            btn.style.transform = "";
          });
        }

        btn.addEventListener("pointerdown", function (e) {
          if (reduceMotion || btn.tagName !== "BUTTON") return;
          var r = btn.getBoundingClientRect();
          var size = Math.max(r.width, r.height) * 2.2;
          var dot = document.createElement("span");
          dot.className = "ripple";
          dot.style.width = dot.style.height = size + "px";
          dot.style.left = (e.clientX - r.left - size / 2) + "px";
          dot.style.top = (e.clientY - r.top - size / 2) + "px";
          btn.appendChild(dot);
          setTimeout(function () { dot.remove(); }, 700);
        });
      })(buttons[i]);
    }

    /* show a loading shimmer on the button that sent the form */
    document.addEventListener("submit", function (e) {
      var btn = e.submitter;
      if (btn && btn.tagName === "BUTTON") {
        setTimeout(function () { btn.classList.add("is-busy"); }, 0);
      }
    });

    /* coming back with the Back button shouldn't leave a button stuck loading */
    window.addEventListener("pageshow", function () {
      document.querySelectorAll("button.is-busy").forEach(function (b) {
        b.classList.remove("is-busy");
      });
    });
  }

  /* ---------- the little schedule on the login page keeps changing ---------- */
  function setupMiniBoard() {
    var rows = document.querySelectorAll(".mini-row");
    if (!rows.length || reduceMotion) return;
    var states = [["ok", "Available"], ["taken", "Booked"], ["wait", "Pending"]];
    var turn = 0;
    setInterval(function () {
      var row = rows[turn % rows.length];
      var em = row.querySelector("em");
      var current = 0;
      for (var i = 0; i < states.length; i++) if (em.classList.contains(states[i][0])) current = i;
      var next = states[(current + 1) % states.length];
      row.classList.remove("flip");
      void row.offsetWidth;
      em.className = next[0];
      em.textContent = next[1];
      row.classList.add("flip");
      turn += 3;
    }, 2200);
  }
})();
