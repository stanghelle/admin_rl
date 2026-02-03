/**
 * File Manager - RL Admin Theme
 * Standalone JavaScript
 */

(function() {
  'use strict';

  // Configuration
  const API_URL = 'fm_api.php';

  // State
  let fileData = null;
  let currentPath = 'files';
  let currentView = 'grid';
  let currentFilter = 'all';
  let selectedItem = null;
  let searchQuery = '';

  // File type categories
  const categories = {
    documents: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv', 'md', 'odt'],
    images: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
    videos: ['mp4', 'mov', 'avi', 'mkv', 'wmv', 'flv', 'webm'],
    audio: ['mp3', 'wav', 'flac', 'aac', 'ogg', 'wma', 'm4a'],
    archives: ['zip', 'rar', '7z', 'tar', 'gz']
  };

  // DOM Elements
  const elements = {
    fileList: document.getElementById('fileList'),
    crumbs: document.getElementById('crumbs'),
    emptyState: document.getElementById('emptyState'),
    loadingState: document.getElementById('loadingState'),
    searchInput: document.getElementById('searchInput'),
    ctxMenu: document.getElementById('ctxMenu'),
    dropZone: document.getElementById('dropZone'),
    fileInput: document.getElementById('fileInput'),
    toastWrap: document.getElementById('toastWrap'),
    uploadBtn: document.getElementById('uploadBtn'),
    newFolderBtn: document.getElementById('newFolderBtn'),
    refreshBtn: document.getElementById('refreshBtn'),
    emptyUploadBtn: document.getElementById('emptyUploadBtn')
  };

  /**
   * Initialize file manager
   */
  function init() {
    loadFiles();
    bindEvents();
    console.log('File Manager initialized');
  }

  /**
   * Load files from server
   */
  function loadFiles() {
    showLoading(true);

    fetch(API_URL + '?action=scan')
      .then(function(response) {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(function(data) {
        console.log('Files loaded:', data);
        fileData = data;
        renderFiles();
        showLoading(false);
      })
      .catch(function(error) {
        console.error('Error loading files:', error);
        showToast('Failed to load files: ' + error.message, 'error');
        showLoading(false);
        elements.emptyState.classList.add('show');
      });
  }

  /**
   * Find items by path
   */
  function findItemsByPath(path) {
    if (!fileData) return [];

    if (path === 'files' || path === fileData.path) {
      return fileData.items || [];
    }

    var parts = path.replace('files/', '').split('/').filter(Boolean);
    var current = fileData.items || [];

    for (var i = 0; i < parts.length; i++) {
      var folder = null;
      for (var j = 0; j < current.length; j++) {
        if (current[j].type === 'folder' && current[j].name === parts[i]) {
          folder = current[j];
          break;
        }
      }
      if (folder) {
        current = folder.items || [];
      } else {
        return [];
      }
    }

    return current;
  }

  /**
   * Render files
   */
  function renderFiles() {
    var items = findItemsByPath(currentPath);

    // Apply filter
    if (currentFilter !== 'all') {
      items = filterItems(items, categories[currentFilter] || []);
    }

    // Apply search
    if (searchQuery) {
      var query = searchQuery.toLowerCase();
      items = items.filter(function(item) {
        return item.name.toLowerCase().indexOf(query) !== -1;
      });
    }

    elements.fileList.innerHTML = '';

    if (items.length === 0) {
      elements.emptyState.classList.add('show');
      return;
    }

    elements.emptyState.classList.remove('show');

    for (var i = 0; i < items.length; i++) {
      var li = createFileElement(items[i]);
      elements.fileList.appendChild(li);
    }

    lucide.createIcons();
    updateBreadcrumbs();
  }

  /**
   * Filter items by extensions
   */
  function filterItems(items, extensions, results) {
    if (!results) results = [];

    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      if (item.type === 'folder') {
        filterItems(item.items || [], extensions, results);
      } else if (extensions.indexOf(item.extension) !== -1) {
        results.push(item);
      }
    }

    return results;
  }

  /**
   * Create file element
   */
  function createFileElement(item) {
    var li = document.createElement('li');
    li.className = 'fm-item';
    li.setAttribute('data-path', item.path);
    li.setAttribute('data-type', item.type);
    li.setAttribute('data-name', item.name);

    var isFolder = item.type === 'folder';
    var ext = item.extension || '';
    var isImage = categories.images.indexOf(ext) !== -1;

    var iconClass = isFolder ? 'folder' : 'file';
    var iconName = isFolder ? 'folder' : 'file';

    // Specific file icons
    if (!isFolder) {
      iconClass += ' type-' + ext;
      if (categories.images.indexOf(ext) !== -1) iconName = 'image';
      else if (categories.videos.indexOf(ext) !== -1) iconName = 'video';
      else if (categories.audio.indexOf(ext) !== -1) iconName = 'music';
      else if (categories.archives.indexOf(ext) !== -1) iconName = 'archive';
      else if (categories.documents.indexOf(ext) !== -1) iconName = 'file-text';
    }

    // Create icon HTML
    var iconHTML;
    if (isImage && currentView === 'grid') {
      iconHTML = '<div class="fm-icon ' + iconClass + ' thumb">' +
        '<img src="' + item.path + '" alt="' + item.name + '" loading="lazy" ' +
        'onerror="this.parentElement.innerHTML=\'<i data-lucide=\\\'image\\\'></i>\';this.parentElement.classList.remove(\'thumb\');lucide.createIcons();">' +
        '</div>';
    } else {
      iconHTML = '<div class="fm-icon ' + iconClass + '"><i data-lucide="' + iconName + '"></i></div>';
    }

    // File info
    var size = item.size ? formatSize(item.size) : '';
    var date = item.modified ? formatDate(item.modified) : '';

    var sizeHTML = '';
    if (currentView === 'list' && size) {
      sizeHTML = '<div class="fm-size">' + size + '</div>';
    }

    li.innerHTML = iconHTML +
      '<div class="fm-info">' +
        '<div class="fm-name" title="' + item.name + '">' + item.name + '</div>' +
        '<div class="fm-meta">' + size + (size && date ? ' • ' : '') + date + '</div>' +
      '</div>' + sizeHTML;

    // Click handler
    li.addEventListener('click', function() {
      if (isFolder) {
        currentPath = item.path;
        renderFiles();
      } else {
        window.open(item.path, '_blank');
      }
    });

    // Context menu handler
    li.addEventListener('contextmenu', function(e) {
      e.preventDefault();
      selectedItem = item;
      showContextMenu(e.clientX, e.clientY);
    });

    return li;
  }

  /**
   * Update breadcrumbs
   */
  function updateBreadcrumbs() {
    var parts = currentPath.split('/').filter(Boolean);
    var html = '';
    var path = '';

    for (var i = 0; i < parts.length; i++) {
      path += (path ? '/' : '') + parts[i];
      var isLast = i === parts.length - 1;

      if (isLast) {
        html += '<span class="cur">' + parts[i] + '</span>';
      } else {
        html += '<a href="#" data-path="' + path + '">' + parts[i] + '</a>';
        html += '<span class="sep">/</span>';
      }
    }

    elements.crumbs.innerHTML = html;

    // Bind breadcrumb clicks
    var links = elements.crumbs.querySelectorAll('a');
    for (var i = 0; i < links.length; i++) {
      links[i].addEventListener('click', function(e) {
        e.preventDefault();
        currentPath = this.getAttribute('data-path');
        renderFiles();
      });
    }
  }

  /**
   * Format file size
   */
  function formatSize(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  }

  /**
   * Format date
   */
  function formatDate(timestamp) {
    var date = new Date(timestamp * 1000);
    var day = ('0' + date.getDate()).slice(-2);
    var month = ('0' + (date.getMonth() + 1)).slice(-2);
    var year = date.getFullYear();
    return day + '.' + month + '.' + year;
  }

  /**
   * Show/hide loading
   */
  function showLoading(show) {
    if (show) {
      elements.loadingState.classList.add('show');
      elements.fileList.style.display = 'none';
    } else {
      elements.loadingState.classList.remove('show');
      elements.fileList.style.display = '';
    }
  }

  /**
   * Show context menu
   */
  function showContextMenu(x, y) {
    elements.ctxMenu.style.left = x + 'px';
    elements.ctxMenu.style.top = y + 'px';
    elements.ctxMenu.classList.add('show');
  }

  /**
   * Hide context menu
   */
  function hideContextMenu() {
    elements.ctxMenu.classList.remove('show');
  }

  /**
   * Show toast notification
   */
  function showToast(message, type) {
    if (!type) type = 'success';

    var toast = document.createElement('div');
    toast.className = 'fm-toast ' + type;

    var iconName = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'alert-circle');

    toast.innerHTML =
      '<div class="fm-toast-icon"><i data-lucide="' + iconName + '"></i></div>' +
      '<div class="fm-toast-msg">' + message + '</div>' +
      '<button class="fm-toast-close"><i data-lucide="x"></i></button>';

    elements.toastWrap.appendChild(toast);
    lucide.createIcons();

    toast.querySelector('.fm-toast-close').addEventListener('click', function() {
      toast.remove();
    });

    setTimeout(function() {
      if (toast.parentNode) toast.remove();
    }, 4000);
  }

  /**
   * Upload files
   */
  function uploadFiles(files) {
    if (!files || files.length === 0) return;

    var formData = new FormData();
    for (var i = 0; i < files.length; i++) {
      formData.append('file[]', files[i]);
    }
    formData.append('path', currentPath);

    showToast('Uploading ' + files.length + ' file(s)...', 'warning');

    fetch(API_URL + '?action=upload', {
      method: 'POST',
      body: formData
    })
      .then(function(response) {
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          showToast(data.message || 'Upload successful', 'success');
          loadFiles();
        } else {
          showToast(data.error || 'Upload failed', 'error');
        }
      })
      .catch(function(error) {
        console.error('Upload error:', error);
        showToast('Upload failed: ' + error.message, 'error');
      });
  }

  /**
   * Create new folder
   */
  function createNewFolder() {
    var name = prompt('Enter folder name:');
    if (!name || !name.trim()) return;

    fetch(API_URL + '?action=newfolder', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ path: currentPath, name: name.trim() })
    })
      .then(function(response) {
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          showToast('Folder created', 'success');
          loadFiles();
        } else {
          showToast(data.error || 'Failed to create folder', 'error');
        }
      })
      .catch(function(error) {
        console.error('Create folder error:', error);
        showToast('Failed to create folder', 'error');
      });
  }

  /**
   * Rename item
   */
  function renameItem(item) {
    var newName = prompt('Enter new name:', item.name);
    if (!newName || !newName.trim() || newName === item.name) return;

    fetch(API_URL + '?action=rename', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ oldPath: item.path, newName: newName.trim() })
    })
      .then(function(response) {
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          showToast('Renamed successfully', 'success');
          loadFiles();
        } else {
          showToast(data.error || 'Failed to rename', 'error');
        }
      })
      .catch(function(error) {
        console.error('Rename error:', error);
        showToast('Failed to rename', 'error');
      });
  }

  /**
   * Delete item
   */
  function deleteItem(item) {
    if (!confirm('Are you sure you want to delete "' + item.name + '"?')) return;

    fetch(API_URL + '?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ path: item.path })
    })
      .then(function(response) {
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          showToast('Deleted successfully', 'success');
          loadFiles();
        } else {
          showToast(data.error || 'Failed to delete', 'error');
        }
      })
      .catch(function(error) {
        console.error('Delete error:', error);
        showToast('Failed to delete', 'error');
      });
  }

  /**
   * Bind all events
   */
  function bindEvents() {
    // Upload button
    if (elements.uploadBtn) {
      elements.uploadBtn.addEventListener('click', function() {
        elements.fileInput.click();
      });
    }

    // Empty state upload button
    if (elements.emptyUploadBtn) {
      elements.emptyUploadBtn.addEventListener('click', function() {
        elements.fileInput.click();
      });
    }

    // File input change
    if (elements.fileInput) {
      elements.fileInput.addEventListener('change', function() {
        uploadFiles(this.files);
        this.value = '';
      });
    }

    // New folder button
    if (elements.newFolderBtn) {
      elements.newFolderBtn.addEventListener('click', createNewFolder);
    }

    // Refresh button
    if (elements.refreshBtn) {
      elements.refreshBtn.addEventListener('click', loadFiles);
    }

    // Search input
    if (elements.searchInput) {
      elements.searchInput.addEventListener('input', function(e) {
        searchQuery = e.target.value;
        renderFiles();
      });
    }

    // View toggle
    var viewBtns = document.querySelectorAll('.fm-view button');
    for (var i = 0; i < viewBtns.length; i++) {
      viewBtns[i].addEventListener('click', function() {
        for (var j = 0; j < viewBtns.length; j++) {
          viewBtns[j].classList.remove('active');
        }
        this.classList.add('active');
        currentView = this.getAttribute('data-view');
        elements.fileList.className = 'fm-list ' + currentView;
        renderFiles();
      });
    }

    // Nav items (filter)
    var navItems = document.querySelectorAll('.fm-nav li');
    for (var i = 0; i < navItems.length; i++) {
      navItems[i].addEventListener('click', function() {
        for (var j = 0; j < navItems.length; j++) {
          navItems[j].classList.remove('active');
        }
        this.classList.add('active');
        currentFilter = this.getAttribute('data-filter');
        currentPath = 'files';
        renderFiles();
      });
    }

    // Context menu actions
    var ctxItems = elements.ctxMenu.querySelectorAll('button');
    for (var i = 0; i < ctxItems.length; i++) {
      ctxItems[i].addEventListener('click', function() {
        var action = this.getAttribute('data-action');
        hideContextMenu();

        if (!selectedItem) return;

        switch (action) {
          case 'open':
            if (selectedItem.type === 'folder') {
              currentPath = selectedItem.path;
              renderFiles();
            } else {
              window.open(selectedItem.path, '_blank');
            }
            break;
          case 'download':
            var a = document.createElement('a');
            a.href = selectedItem.path;
            a.download = selectedItem.name;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            break;
          case 'rename':
            renameItem(selectedItem);
            break;
          case 'delete':
            deleteItem(selectedItem);
            break;
        }
      });
    }

    // Hide context menu on click outside
    document.addEventListener('click', hideContextMenu);

    // Drag and drop
    var container = document.querySelector('.fm-content');
    if (container) {
      container.addEventListener('dragover', function(e) {
        e.preventDefault();
        elements.dropZone.classList.add('active');
      });

      container.addEventListener('dragleave', function(e) {
        if (!container.contains(e.relatedTarget)) {
          elements.dropZone.classList.remove('active');
        }
      });

      container.addEventListener('drop', function(e) {
        e.preventDefault();
        elements.dropZone.classList.remove('active');
        uploadFiles(e.dataTransfer.files);
      });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        hideContextMenu();
      }

      if (e.key === 'Backspace' && currentPath !== 'files') {
        var activeEl = document.activeElement;
        if (activeEl.tagName !== 'INPUT' && activeEl.tagName !== 'TEXTAREA') {
          e.preventDefault();
          var parts = currentPath.split('/');
          parts.pop();
          currentPath = parts.join('/') || 'files';
          renderFiles();
        }
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
