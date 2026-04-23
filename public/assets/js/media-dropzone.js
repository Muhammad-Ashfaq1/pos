(function (window, document) {
  'use strict';

  const defaultPreviewTemplate = function () {
    return (
      '<div class="dz-preview dz-file-preview">' +
        '<div class="dz-details">' +
          '<div class="dz-thumbnail">' +
            '<img data-dz-thumbnail alt="Preview">' +
            '<span class="dz-nopreview">No preview</span>' +
          '</div>' +
          '<div class="dz-filename" data-dz-name></div>' +
          '<div class="dz-size" data-dz-size></div>' +
        '</div>' +
      '</div>'
    );
  };

  const ensureDropzone = function () {
    return typeof window.Dropzone !== 'undefined';
  };

  const toInt = function (value) {
    const parsed = parseInt(value, 10);
    return Number.isNaN(parsed) ? null : parsed;
  };

  function MediaDropzone(element, options) {
    this.element = element;
    this.options = options || {};
    this.hiddenInput = document.getElementById(element.id + '_input');
    this.primaryInput = document.getElementById(element.id + '_primary');
    this.removedContainer = document.getElementById(element.id + '_removed');
    this.feedback = document.getElementById(element.id + '_feedback');
    this.maxFiles = toInt(element.closest('[data-media-dropzone]')?.dataset.maxFiles) || 20;
    this.existingImages = [];
    this.removedImageIds = [];
    this.primaryRef = '';
    this.isRefreshingPrimary = false;
    this.isResetting = false;
    this.init();
  }

  MediaDropzone.prototype.init = function () {
    if (!ensureDropzone()) {
      return;
    }

    const self = this;

    this.dropzone = new window.Dropzone(this.element, {
      url: '#',
      autoProcessQueue: false,
      clickable: true,
      addRemoveLinks: true,
      parallelUploads: 1,
      maxFilesize: 5,
      acceptedFiles: this.hiddenInput.getAttribute('accept'),
      previewTemplate: defaultPreviewTemplate(),
      previewsContainer: this.element.querySelector('.dz-preview-container'),
      init: function () {
        this.on('addedfile', function (file) {
          if (self.totalCount() > self.maxFiles) {
            this.removeFile(file);
            self.showError('You can upload up to ' + self.maxFiles + ' images.');
            return;
          }

          self.decorateFile(file);
          self.syncFileInput();
          self.refreshPrimaryState();
          self.refreshSurfaceState();
          self.clearError();
        });

        this.on('removedfile', function (file) {
          if (self.isResetting) {
            return;
          }

          if (file.existingImageId) {
            self.markExistingImageRemoved(file.existingImageId);
          }

          self.syncFileInput();
          self.refreshPrimaryState();
          self.refreshSurfaceState();
        });
      }
    });
  };

  MediaDropzone.prototype.totalCount = function () {
    if (!this.dropzone) {
      return this.existingImages.filter(function (image) {
        return !image.removed;
      }).length;
    }

    const existingActive = this.existingImages.filter(function (image) {
      return !image.removed;
    }).length;
    const newFiles = this.dropzone.files.filter(function (file) {
      return !file.existingImageId;
    }).length;

    return existingActive + newFiles;
  };

  MediaDropzone.prototype.decorateFile = function (file) {
    if (!file.previewElement) {
      return;
    }

    const self = this;
    const thumbnailImage = file.previewElement.querySelector('[data-dz-thumbnail]');
    const noPreview = file.previewElement.querySelector('.dz-nopreview');
    const removeLink = file.previewElement.querySelector('.dz-remove');
    const meta = document.createElement('div');
    meta.className = 'app-media-dropzone__meta';

    if (removeLink) {
      removeLink.setAttribute('title', 'Remove image');
      removeLink.setAttribute('aria-label', 'Remove image');
      removeLink.textContent = 'Remove image';
    }

    if (thumbnailImage && noPreview) {
      const syncPreviewVisibility = function () {
        const hasPreview = thumbnailImage.getAttribute('src');
        noPreview.style.display = hasPreview ? 'none' : '';
      };

      thumbnailImage.addEventListener('load', syncPreviewVisibility);
      thumbnailImage.addEventListener('error', syncPreviewVisibility);
      syncPreviewVisibility();
    }

    const sourceBadge = document.createElement('span');
    sourceBadge.className = 'badge bg-label-secondary';
    sourceBadge.textContent = file.existingImageId ? 'Saved' : 'New';
    meta.appendChild(sourceBadge);

    const primaryBadge = document.createElement('span');
    primaryBadge.className = 'badge bg-label-primary d-none';
    primaryBadge.textContent = 'Primary';
    meta.appendChild(primaryBadge);

    const primaryButton = document.createElement('button');
    primaryButton.type = 'button';
    primaryButton.className = 'btn btn-sm btn-label-primary';
    primaryButton.textContent = 'Set Primary';
    primaryButton.addEventListener('click', function () {
      self.setPrimaryReference(file.existingImageId ? 'existing:' + file.existingImageId : 'new:' + self.newFileIndex(file));
    });
    meta.appendChild(primaryButton);

    file.previewElement.appendChild(meta);
    file._primaryBadge = primaryBadge;
    file._primaryButton = primaryButton;
  };

  MediaDropzone.prototype.newFileIndex = function (targetFile) {
    let index = -1;

    this.dropzone.files.forEach(function (file) {
      if (file.existingImageId) {
        return;
      }

      index += 1;

      if (file === targetFile) {
        targetFile._newImageIndex = index;
      }
    });

    return targetFile._newImageIndex || 0;
  };

  MediaDropzone.prototype.syncFileInput = function () {
    if (!this.hiddenInput) {
      return;
    }

    const transfer = new DataTransfer();

    this.dropzone.files.forEach(function (file) {
      if (!file.existingImageId) {
        transfer.items.add(file);
      }
    });

    this.hiddenInput.files = transfer.files;
  };

  MediaDropzone.prototype.markExistingImageRemoved = function (imageId) {
    this.existingImages = this.existingImages.map(function (image) {
      if (image.id === imageId) {
        image.removed = true;
      }

      return image;
    });

    if (!this.removedImageIds.includes(imageId)) {
      this.removedImageIds.push(imageId);
    }

    this.renderRemovedInputs();

    if (this.primaryRef === 'existing:' + imageId) {
      this.primaryRef = '';
    }
  };

  MediaDropzone.prototype.renderRemovedInputs = function () {
    const inputName = this.options.removedInputName || 'removed_image_ids[]';
    this.removedContainer.innerHTML = '';

    this.removedImageIds.forEach(function (imageId) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = inputName;
      input.value = String(imageId);
      this.removedContainer.appendChild(input);
    }, this);
  };

  MediaDropzone.prototype.setPrimaryReference = function (value) {
    this.primaryRef = value || '';

    if (this.primaryInput) {
      this.primaryInput.value = this.primaryRef;
    }

    this.refreshPrimaryState();
  };

  MediaDropzone.prototype.findFirstAvailablePrimaryRef = function (activeFiles) {
    const firstExisting = this.existingImages.find(function (image) {
      return !image.removed;
    });

    if (firstExisting) {
      return 'existing:' + firstExisting.id;
    }

    const firstNew = (activeFiles || []).find(function (file) {
      return !file.existingImageId;
    });

    if (firstNew) {
      return 'new:' + this.newFileIndex(firstNew);
    }

    return '';
  };

  MediaDropzone.prototype.refreshPrimaryState = function () {
    if (this.isRefreshingPrimary || !this.dropzone) {
      return;
    }

    this.isRefreshingPrimary = true;

    const activeFiles = this.dropzone.files.filter(function (file) {
      return !file._removeLink || file.status !== 'canceled';
    });

    try {
      let currentPrimary = this.primaryRef;

      if (!currentPrimary) {
        currentPrimary = this.findFirstAvailablePrimaryRef(activeFiles);
      }

      const primaryStillExists = currentPrimary
        ? this.dropzone.files.some(function (file) {
            return (file.existingImageId ? 'existing:' + file.existingImageId : 'new:' + this.newFileIndex(file)) === currentPrimary;
          }, this)
        : false;

      if (!primaryStillExists) {
        currentPrimary = this.findFirstAvailablePrimaryRef(activeFiles);
      }

      this.primaryRef = currentPrimary || '';

      if (this.primaryInput) {
        this.primaryInput.value = this.primaryRef;
      }

      this.dropzone.files.forEach(function (file) {
        const ref = file.existingImageId ? 'existing:' + file.existingImageId : 'new:' + this.newFileIndex(file);
        const isPrimary = this.primaryRef === ref;

        if (file._primaryBadge) {
          file._primaryBadge.classList.toggle('d-none', !isPrimary);
        }

        if (file._primaryButton) {
          file._primaryButton.textContent = isPrimary ? 'Primary Selected' : 'Set Primary';
          file._primaryButton.classList.toggle('btn-label-primary', !isPrimary);
          file._primaryButton.classList.toggle('btn-primary', isPrimary);
        }
      }, this);
    } finally {
      this.isRefreshingPrimary = false;
    }
  };

  MediaDropzone.prototype.refreshSurfaceState = function () {
    this.element.classList.toggle('has-files', this.totalCount() > 0);
  };

  MediaDropzone.prototype.showError = function (message) {
    if (this.feedback) {
      this.feedback.textContent = message || '';
    }
  };

  MediaDropzone.prototype.clearError = function () {
    this.showError('');
  };

  MediaDropzone.prototype.reset = function () {
    this.isResetting = true;
    this.existingImages = [];
    this.removedImageIds = [];
    this.primaryRef = '';
    this.renderRemovedInputs();
    this.setPrimaryReference('');
    this.clearError();

    if (this.dropzone) {
      this.dropzone.removeAllFiles(true);
    }

    if (this.hiddenInput) {
      this.hiddenInput.value = '';
    }

    this.refreshSurfaceState();
    this.isResetting = false;
  };

  MediaDropzone.prototype.loadExisting = function (images) {
    this.reset();

    const self = this;
    this.existingImages = Array.isArray(images) ? images.map(function (image) {
      return Object.assign({}, image, { removed: false });
    }) : [];

    this.existingImages.forEach(function (image) {
      const mockFile = {
        name: image.original_name || image.file_name || ('Image #' + image.id),
        size: image.size || 0,
        accepted: true,
        existingImageId: image.id,
        status: window.Dropzone.SUCCESS
      };

      self.dropzone.emit('addedfile', mockFile);
      self.dropzone.emit('thumbnail', mockFile, image.url);
      self.dropzone.emit('complete', mockFile);
      self.dropzone.files.push(mockFile);

      if (image.is_primary) {
        self.setPrimaryReference('existing:' + image.id);
      }
    });

    this.syncFileInput();
    this.refreshPrimaryState();
    this.refreshSurfaceState();
  };

  window.AppMediaDropzone = {
    create: function (element, options) {
      if (!element || !ensureDropzone()) {
        return null;
      }

      return new MediaDropzone(element, options || {});
    }
  };
})(window, document);
