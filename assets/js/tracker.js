/**
 * RL Admin Traffic Tracker
 * Lightweight script to track page visits on your public website
 *
 * Usage: Add this script to your website:
 * <script src="https://your-admin-domain.com/assets/js/tracker.js" data-api="https://your-admin-domain.com/api/traffic.php"></script>
 *
 * Or initialize manually:
 * <script>
 *   window.RLTracker = { apiUrl: 'https://your-admin-domain.com/api/traffic.php' };
 * </script>
 * <script src="https://your-admin-domain.com/assets/js/tracker.js"></script>
 */

(function() {
    'use strict';

    // Configuration
    var config = {
        apiUrl: '',
        trackOnLoad: true,
        trackSPA: true,           // Track single-page app navigation
        excludePaths: [],         // Paths to exclude from tracking
        excludeParams: ['token', 'password', 'key'], // Query params to strip
        sessionDuration: 30 * 60 * 1000, // 30 minutes
        debug: false
    };

    // Get API URL from script tag or global config
    function getApiUrl() {
        // Check global config first
        if (window.RLTracker && window.RLTracker.apiUrl) {
            return window.RLTracker.apiUrl;
        }

        // Check script tag data attribute
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].src || '';
            if (src.indexOf('tracker.js') !== -1) {
                var apiUrl = scripts[i].getAttribute('data-api');
                if (apiUrl) return apiUrl;

                // Try to construct API URL from script src
                var match = src.match(/^(https?:\/\/[^\/]+)/);
                if (match) {
                    return match[1] + '/api/traffic.php';
                }
            }
        }

        // Fallback: assume same domain
        return '/api/traffic.php';
    }

    // Initialize config
    config.apiUrl = getApiUrl();

    // Merge user config
    if (window.RLTracker) {
        for (var key in window.RLTracker) {
            if (window.RLTracker.hasOwnProperty(key)) {
                config[key] = window.RLTracker[key];
            }
        }
    }

    // Session management
    function getSessionId() {
        var sessionKey = 'rl_tracker_session';
        var session = null;

        try {
            var stored = localStorage.getItem(sessionKey);
            if (stored) {
                session = JSON.parse(stored);
                // Check if session is still valid
                if (Date.now() - session.lastActivity > config.sessionDuration) {
                    session = null;
                }
            }
        } catch (e) {}

        if (!session) {
            session = {
                id: generateId(),
                started: Date.now(),
                lastActivity: Date.now()
            };
        } else {
            session.lastActivity = Date.now();
        }

        try {
            localStorage.setItem(sessionKey, JSON.stringify(session));
        } catch (e) {}

        return session.id;
    }

    // Generate unique ID
    function generateId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            var v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Clean URL - remove sensitive parameters
    function cleanUrl(url) {
        try {
            var urlObj = new URL(url, window.location.origin);
            config.excludeParams.forEach(function(param) {
                urlObj.searchParams.delete(param);
            });
            return urlObj.pathname + urlObj.search;
        } catch (e) {
            return url;
        }
    }

    // Check if path should be excluded
    function shouldExclude(path) {
        for (var i = 0; i < config.excludePaths.length; i++) {
            var pattern = config.excludePaths[i];
            if (typeof pattern === 'string' && path.indexOf(pattern) !== -1) {
                return true;
            }
            if (pattern instanceof RegExp && pattern.test(path)) {
                return true;
            }
        }
        return false;
    }

    // Track a page view
    function trackPageView(customData) {
        var pageUrl = cleanUrl(window.location.href);

        // Check exclusions
        if (shouldExclude(pageUrl)) {
            if (config.debug) console.log('[RLTracker] Excluded:', pageUrl);
            return;
        }

        var data = {
            action: 'record',
            page_url: pageUrl,
            page_title: document.title || '',
            referrer: document.referrer || '',
            session_id: getSessionId(),
            screen_width: window.screen.width,
            screen_height: window.screen.height,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
            language: navigator.language || ''
        };

        // Merge custom data
        if (customData) {
            for (var key in customData) {
                if (customData.hasOwnProperty(key)) {
                    data[key] = customData[key];
                }
            }
        }

        // Send tracking data
        sendData(data);

        if (config.debug) console.log('[RLTracker] Tracked:', pageUrl);
    }

    // Send data to API
    function sendData(data) {
        // Use sendBeacon if available (doesn't block page unload)
        if (navigator.sendBeacon) {
            var formData = new FormData();
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    formData.append(key, data[key]);
                }
            }
            navigator.sendBeacon(config.apiUrl + '?action=record', formData);
        } else {
            // Fallback to XMLHttpRequest
            var xhr = new XMLHttpRequest();
            xhr.open('POST', config.apiUrl + '?action=record', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            var params = [];
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
                }
            }
            xhr.send(params.join('&'));
        }
    }

    // Track SPA navigation (for React, Vue, Angular, etc.)
    function setupSPATracking() {
        if (!config.trackSPA) return;

        // Track history changes
        var originalPushState = history.pushState;
        var originalReplaceState = history.replaceState;

        history.pushState = function() {
            originalPushState.apply(this, arguments);
            setTimeout(trackPageView, 0);
        };

        history.replaceState = function() {
            originalReplaceState.apply(this, arguments);
            setTimeout(trackPageView, 0);
        };

        // Track popstate (back/forward buttons)
        window.addEventListener('popstate', function() {
            setTimeout(trackPageView, 0);
        });
    }

    // Track outbound links (optional)
    function trackOutboundLink(url, callback) {
        sendData({
            action: 'record',
            page_url: 'outbound:' + url,
            page_title: 'Outbound Link',
            referrer: window.location.href
        });

        if (typeof callback === 'function') {
            setTimeout(callback, 100);
        }
    }

    // Public API
    window.RLTracker = window.RLTracker || {};
    window.RLTracker.track = trackPageView;
    window.RLTracker.trackOutbound = trackOutboundLink;
    window.RLTracker.config = config;

    // Initialize
    function init() {
        if (config.debug) console.log('[RLTracker] Initialized with API:', config.apiUrl);

        setupSPATracking();

        if (config.trackOnLoad) {
            // Track initial page view
            if (document.readyState === 'complete') {
                trackPageView();
            } else {
                window.addEventListener('load', function() {
                    trackPageView();
                });
            }
        }
    }

    // Start tracking
    init();

})();
