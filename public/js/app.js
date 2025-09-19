// Vue.js Application
const { createApp } = Vue;

const app = createApp({
  data() {
    return {
      loading: false,
      darkMode: false,
      stats: {
        totalRecords: 0,
        markedRecords: 0,
        lastUpdate: null
      },
      notifications: []
    }
  },

  mounted() {
    this.initializeApp();
    this.checkDarkMode();
  },

  methods: {
    initializeApp() {
      // Initialize any startup logic
      this.loadStats();
    },

    loadStats() {
      // This would typically load from an API
      // For now, read from DOM if available
      const totalElement = document.getElementById('totalCount');
      const markedElement = document.getElementById('markedCount');

      if (totalElement) {
        this.stats.totalRecords = parseInt(totalElement.textContent) || 0;
      }
      if (markedElement) {
        this.stats.markedRecords = parseInt(markedElement.textContent) || 0;
      }
    },

    checkDarkMode() {
      this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    },

    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      document.documentElement.classList.toggle('dark', this.darkMode);
    },

    showNotification(message, type = 'info', duration = 5000) {
      const notification = {
        id: Date.now(),
        message,
        type,
        visible: true
      };

      this.notifications.push(notification);

      setTimeout(() => {
        this.hideNotification(notification.id);
      }, duration);
    },

    hideNotification(id) {
      const index = this.notifications.findIndex(n => n.id === id);
      if (index > -1) {
        this.notifications.splice(index, 1);
      }
    },

    // Data table methods
    markRecord(id, marked) {
      this.loading = true;

      return fetch('/mark', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id, marked })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          this.stats.markedRecords += marked ? 1 : -1;
          this.showNotification(
            marked ? 'Záznam označen' : 'Označení zrušeno',
            'success',
            2000
          );
        } else {
          throw new Error(data.message || 'Chyba při označování');
        }
        return data;
      })
      .catch(error => {
        this.showNotification('Chyba při označování záznamu: ' + error.message, 'error');
        throw error;
      })
      .finally(() => {
        this.loading = false;
      });
    },

    clearAllMarks() {
      if (!confirm('Are you sure you want to clear all selections?')) {
        return;
      }

      this.loading = true;
      const markedRows = document.querySelectorAll('tr.row-marked');

      Promise.all(Array.from(markedRows).map(row => {
        const id = row.dataset.id;
        return this.markRecord(id, false);
      }))
      .then(() => {
        markedRows.forEach(row => {
          row.classList.remove('row-marked');
          updateStatusBadge($(row), false);
        });
        this.stats.markedRecords = 0;
        this.showNotification('All selections cleared', 'success');
      })
      .catch(error => {
        this.showNotification('Error clearing selections', 'error');
      })
      .finally(() => {
        this.loading = false;
      });
    },

    // Export functionality
    exportData(format) {
      this.loading = true;

      fetch('/export?format=' + format)
        .then(response => {
          if (!response.ok) throw new Error('Export failed');
          return response.blob();
        })
        .then(blob => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `data_export_${new Date().toISOString().slice(0,10)}.${format}`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);

          this.showNotification(`Data exportována do ${format.toUpperCase()}`, 'success');
        })
        .catch(error => {
          this.showNotification('Chyba při exportu: ' + error.message, 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    // Utility methods
    formatDate(date) {
      return new Date(date).toLocaleDateString('cs-CZ');
    },

    formatNumber(number) {
      return new Intl.NumberFormat('cs-CZ').format(number);
    }
  }
});

// Mount the Vue app only if #app element exists
if (document.getElementById('app')) {
  window.app = app.mount('#app');
} else {
  console.log('Vue app not mounted - #app element not found');
}

// Enhanced jQuery functionality for backward compatibility
$(document).ready(function() {
  // Note: Row clicking functionality is now handled in individual page scripts
  // to avoid conflicts

  // Smooth transitions for better UX
  $('tr').hover(
    function() {
      $(this).addClass('hover');
    },
    function() {
      $(this).removeClass('hover');
    }
  );
});

// Function to update status badge
function updateStatusBadge($row, isMarked) {
  const statusCell = $row.find('td:last-child');
  if (isMarked) {
    statusCell.html('<span class="badge badge-success" title="Selected">SELECTED</span>');
  } else {
    statusCell.html('<span class="badge badge-secondary" title="Not selected">NORMAL</span>');
  }
}

// Global utilities
window.AppUtils = {
  showLoader: function() {
    if (!document.querySelector('.global-loader')) {
      const loader = document.createElement('div');
      loader.className = 'global-loader';
      loader.innerHTML = '<div class="spinner"></div>';
      document.body.appendChild(loader);
    }
  },

  hideLoader: function() {
    const loader = document.querySelector('.global-loader');
    if (loader) {
      loader.remove();
    }
  },

  formatFileSize: function(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
};