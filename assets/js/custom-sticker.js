var Stickers = Stickers || {};

Stickers.colorMapping = {
  wit: '#FFFFFF',
  zwart: '#000000',
  goudgeel: '#FFB600',
  zonnegeel: '#FFD200',
  creme: '#EFDB9B',
  donkerrood: '#8B101A',
  bloedrood: '#E53935',
  oranje: '#EF5600',
  bordeaux: '#69002E',
  lichtviolet: '#592C87',
  telemagenta: '#C02C6E',
  zachtroze: '#E97FB4',
  staalblauw: '#0D173D',
  diepblauw: '#0C228C',
  verkeersblauw: '#005093',
  ijsblauw: '#90CAF9',
  turkoois: '#48C9B0',
  donkergroen: '#004627',
  grasgroen: '#007A42',
  geelgroen: '#8CD228',
  bruin: '#44291E',
  donkergrijs: '#4C4E4F',
  middengrijs: '#AFAAAA',
  lichtgrijs: '#EBEBEB',
  zilvergrijs: '#A5A69F',
  goud: '#A78D37',
  anthracite: '#383C3E',
};

var CustomSticker = (function ($) {
  var throttle = function (fn, threshhold, scope) {
    threshhold = threshhold || (threshhold = 250);
    var last, deferTimer;
    return function () {
      var context = scope || this;
      var now = +new Date(),
        args = arguments;
      if (last && now < last + threshhold) {
        // hold on to it
        clearTimeout(deferTimer);
        deferTimer = setTimeout(function () {
          last = now;
          fn.apply(context, args);
        }, threshhold);
      } else {
        last = now;
        fn.apply(context, args);
      }
    };
  };

  var formatLength = function (mmValue) {
    return mmValue + ' mm';
  };

  var translateBooleanSlug = function (value, defaultValue) {
    defaultValue = defaultValue || 'no';
    if (!value) {
      return defaultValue;
    }
    return value.toLowerCase() === 'ja' ? 'yes' : 'no';
  };

  var getSelectedOptionLabel = function (select) {
    return $.trim(select.find('option:selected').text());
  };

  var updateSummaryDisplay = function (form, property, value) {
    var displaySelector = Selectors.WIDGET.DISPLAY.SELECTED;
    var inputSelector = Selectors.WIDGET.INPUT;
    if (property === 'fontName') {
      var select = form.find(Selectors.WIDGET.INPUT.FONT);
      var selectedFontLabel = select
        .find('option[value="' + select.val() + '"]')
        .text();
      // Show the selected font
      $(displaySelector.FONT).text(selectedFontLabel);
      // Change the styling of any Naam input. The font face in CSS are prefixed with "ft-" to prevent
      // collisions with existing fonts (like Arial)
      $(inputSelector.NAME).css({ fontFamily: 'ft-' + value });
    } else if (property === 'alignment') {
      $(displaySelector.ALIGNMENT).text(
        $.trim(
          form
            .find(inputSelector.ALIGNMENT + ':checked')
            .parent()
            .text()
        )
      );
    } else if (property === 'mirrored') {
      $(displaySelector.MIRRORED).text(
        $.trim(
          form
            .find(inputSelector.MIRRORED + ':checked')
            .parent()
            .text()
        )
      );
    } else if (property === 'fgColor') {
      $(displaySelector.COLOR).text(
        $.trim(
          form
            .find(inputSelector.FG_COLOR + ':checked')
            .parent()
            .find('span')
            .text()
        )
      );
    } else if (property === 'width') {
      $(displaySelector.WIDTH).text(formatLength(value));
      form.find(Selectors.WIDGET.DISPLAY.WIDTH).text(value);
      $(inputSelector.WIDTH).val(value);
    } else if (property === 'capHeight') {
      $(displaySelector.CAP_HEIGHT).text(formatLength(value));
      $(inputSelector.CAP_HEIGHT).val(value);
    } else if (property === 'height') {
      form.find(displaySelector.HEIGHT).text(formatLength(value));
      form.find(Selectors.WIDGET.DISPLAY.HEIGHT).text(value);
    }
  };

  var SIZING_METHOD = {
    CAP_HEIGHT: 'Kapitaalhoogte',
    WIDTH: 'Breedte',
  };

  var Selectors = {
    ERROR_MESSAGES: {
      HEIGHT: {
        // Shown when the sticker height is too high
        TOO_HIGH: '.js-sticker-error-height--too-high',
        // Shown when the sticker height is too low
        TOO_LOW: '.js-sticker-error-height--too-low',
      },
    },
    MARKERS: {
      HORIZONTAL: '.custom-sticker__sample-hor',
      VERTICAL: '.custom-sticker__sample-ver',
    },
    WIDGET: {
      INPUT: {
        TEXT: '.wc-pao-addon-tekst textarea',
        NAME: '.wc-pao-addon-naam input',
        FONT: '.js-product-attribute--pa_font select',
        ALIGNMENT: '.js-product-attribute--pa_alignment input[type="radio"]',
        MIRRORED: '.js-product-attribute--pa_mirrored input[type="radio"]',
        BG_COLOR: '.js-sticker-bg-colors input',
        FG_COLOR: '.js-sticker-fg-colors input',
        TRANSPARENT: '.js-sticker-transparent input[type="checkbox"]',
        WIDTH: '.wc-pao-addon-breedte input',
        CAP_HEIGHT: '.wc-pao-addon-kapitaalhoogte input',
        WIDTH_RULES: '.wc-pao-addon-breedte_regels select',
        SIZING_METHOD: '.wc-pao-addon-tekstgrootte select',
      },
      WRAPPERS: {
        WIDTH: '.wc-pao-addon-breedte',
        CAP_HEIGHT: '.wc-pao-addon-kapitaalhoogte',
      },
      DISPLAY: {
        HEIGHT: '.js-sticker-height',
        WIDTH: '.js-sticker-width',
        SELECTED: {
          FONT: '.js-custom-sticker-output-font',
          ALIGNMENT: '.js-custom-sticker-output-alignment',
          MIRRORED: '.js-custom-sticker-output-mirrored',
          CAP_HEIGHT: '.js-custom-sticker-output-cap-height',
          HEIGHT: '.js-custom-sticker-output-height',
          WIDTH: '.js-custom-sticker-output-width',
          COLOR: '.js-custom-sticker-output-color',
        },
      },
    },
  };

  // Default values for all parameters/attributes.
  var defaultValues = {
    fontName: 'Arial',
    sizingMethod: SIZING_METHOD.WIDTH,
    width: 400,
    capHeight: 40,
    mirrored: 'no',
    fgColor: 'black',
    bgColor: 'transparent',
    alignment: 'middle',
    lineHeight: 1.4,
    text: 'Jouw tekst hier',
  };
  // We base our available attributes on the keys defined in the defaults.
  var availableAttributes = $.map(defaultValues, function (value, key) {
    return key;
  });

  // The StickerModel holds all state related to the current sticker configuration.
  var StickerModel = function (options) {
    var self = this;

    this.initialize = function (options) {
      options = $.extend({}, defaultValues, options);
      availableAttributes.forEach(function (name) {
        self[name] = options[name];
      });
    };

    this.isSizingByCapHeight = function () {
      return self.sizingMethod === SIZING_METHOD.CAP_HEIGHT;
    };

    self.initialize(options);
  };

  var SampleMode = {
    FULLY_CUSTOM: 'full-custom',
    SIMPLE: 'simple',
  };

  // The StickerSample renders the sample and responds to form and history events.
  var StickerSample = function (options) {
    // The form
    this.form = options.form;
    // The model containing the data
    this.model = options.model;
    this.canvas = null;
    this.svg = null;
    this.sampleElement = null;
    this.mode = SampleMode.SIMPLE;
    this.render = null;
    var self = this;

    // Bootstraps the sample for display based on the correct state.
    this.bootstrap = function () {
      var form = self.form;
      var postRenderingBootstrap = null;
      var inputSelectors = Selectors.WIDGET.INPUT;
      // Check whether we need the full custom sticker or offer simple editing (like color and mirroring)
      if (form.data('fullCustomSticker') === 'yes') {
        self.mode = SampleMode.FULLY_CUSTOM;
      } else {
        self.mode = SampleMode.SIMPLE;
      }
      if (self.mode === SampleMode.FULLY_CUSTOM) {
        // Attach the svg editor render function
        self.render = self.renderPreviewImage;
        // Grab our canvas and set up our SVG utility
        self.canvas = self.form.find('.js-sticker-canvas');
        self.canvas.empty().append('<img src="#" alt="Even geduld aub">');
        self.img = self.canvas.find('img');
        // Restore our model from the information in the URL and then restore our form based on the model.
        self.initializeModel();
        self.toggleSizingMethodAttributes();
        updateSummaryDisplay(self.form, 'mirrored', self.model.mirrored);
        updateSummaryDisplay(self.form, 'fontName', self.model.fontName);
        updateSummaryDisplay(self.form, 'alignment', self.model.alignment);
        updateSummaryDisplay(self.form, 'fgColor', self.model.fgColor);
        form.on('stickerPropertyChanged', function (event, property, value) {
          updateSummaryDisplay(form, property, value);
        });
        postRenderingBootstrap = function () {
          // Now hook up our event listeners
          form.on(
            'keyup',
            inputSelectors.TEXT,
            throttle(self.whenTextChanged, 500)
          );
          form.on('click', inputSelectors.TEXT, function () {
            this.select();
          });
          form.on('change', inputSelectors.TEXT, self.whenTextChanged);
          form.on(
            'change',
            inputSelectors.ALIGNMENT,
            self.whenAlignmentChanged
          );
          form.on('change', inputSelectors.MIRRORED, self.whenMirroringChanged);
          form.on('change', inputSelectors.BG_COLOR, self.whenBgColorChanged);
          form.on('change', inputSelectors.FG_COLOR, self.whenFgColorChanged);
          form.on('change', inputSelectors.FONT, self.whenFontChanged);
          form.on(
            'change',
            inputSelectors.TRANSPARENT,
            self.whenTransparencyChanged
          );
          form.on(
            'change',
            inputSelectors.SIZING_METHOD,
            self.whenSizingMethodChanged
          );
          form.on(
            'change',
            inputSelectors.CAP_HEIGHT,
            throttle(self.whenCapHeightChanged, 500)
          );
          form.on(
            'change',
            inputSelectors.WIDTH,
            throttle(self.whenWidthChanged, 500)
          );
          form.on(
            'input',
            inputSelectors.CAP_HEIGHT,
            throttle(self.whenCapHeightChanged, 500)
          );
          form.on(
            'input',
            inputSelectors.WIDTH,
            throttle(self.whenWidthChanged, 500)
          );
        };
      } else {
        // Either use the simple <img> rendering or use an inline svg. Because Wordpress doesn't support
        // svg thumbnails out of the box, we await the results of svg-inline which will grab the src of the
        // image and embed it in the html. This allows us to tweak the colors.
        self.sampleElement = self.form
          .closest('.summary')
          .prev('.images')
          .find('.wp-post-image');
        if (self.sampleElement.hasClass('style-svg')) {
          self.render = self.renderInlineSvg;
          self.waitForInlineSvg();
        } else {
          self.render = self.renderImage;
        }
        // Restore our model from the information in the URL and then restore our form based on the model.
        self.initializeModel();
        updateSummaryDisplay(self.form, 'fontName', self.model.fontName);
        updateSummaryDisplay(self.form, 'fgColor', self.model.fgColor);
        form.on('stickerPropertyChanged', function (event, property, value) {
          updateSummaryDisplay(form, property, value);
        });
        // Now hook up our event listeners
        form.on('change', inputSelectors.ALIGNMENT, this.whenAlignmentChanged);
        form.on('change', inputSelectors.FONT, this.whenFontChanged);
        form.on('change', inputSelectors.MIRRORED, this.whenMirroringChanged);
        form.on('change', inputSelectors.BG_COLOR, this.whenBgColorChanged);
        form.on('change', inputSelectors.FG_COLOR, this.whenFgColorChanged);
        form.on(
          'change',
          inputSelectors.TRANSPARENT,
          this.whenTransparencyChanged
        );
      }
      // Set up done. Render!
      self.render(postRenderingBootstrap);
    };

    this.initializeModel = function () {
      var form = self.form;
      var inputSelectors = Selectors.WIDGET.INPUT;
      var params = new URLSearchParams(window.location.search);
      var getParam = function (name) {
        var value = params.get(name);
        return value ? atob(value) : '';
      };
      var element, name, indexing, value;
      element = form.find(inputSelectors.TEXT);
      if (element.length) {
        name = element.attr('name');
        indexing = name.indexOf('[');
        if (indexing >= 0) {
          name = name.substring(0, indexing);
        }
        value = getParam(name) || defaultValues.text;
        element.val(value);
      }
      element = self.form.find(inputSelectors.WIDTH);
      if (element.length) {
        name = element.attr('name');
        value = getParam(name) || defaultValues.width;
        element.val(value);
      }
      element = self.form.find(inputSelectors.CAP_HEIGHT);
      if (element.length) {
        name = element.attr('name');
        value = getParam(name) || defaultValues.capHeight;
        element.val(value);
      }
      element = self.form.find(inputSelectors.SIZING_METHOD);
      if (element.length) {
        name = element.attr('name');
        value = getParam(name) || defaultValues.sizingMethod;
        element.val(
          element
            .find('option[value|="' + value.toLowerCase() + '"]')
            .attr('value')
        );
      }
      var model = self.model;
      model.text = form.find(inputSelectors.TEXT).val();
      model.alignment = form.find(inputSelectors.ALIGNMENT + ':checked').val();
      model.mirrored = translateBooleanSlug(
        form.find(inputSelectors.MIRRORED + ':checked').val()
      );
      model.fgColor = form.find(inputSelectors.FG_COLOR + ':checked').val();
      model.fontName = form.find(inputSelectors.FONT).val();
      model.capHeight = form.find(inputSelectors.CAP_HEIGHT).val();
      model.width = form.find(inputSelectors.WIDTH).val();
      model.sizingMethod = getSelectedOptionLabel(
        form.find(inputSelectors.SIZING_METHOD)
      );
    };

    this.whenTextChanged = function () {
      var oldText = self.model.text;
      self.model.text = $(this).val();
      if (oldText === self.model.text) {
        return;
      }
      self.render();
    };

    this.whenSizingMethodChanged = function () {
      self.model.sizingMethod = getSelectedOptionLabel($(this));
      self.toggleSizingMethodAttributes();
      self.triggerPropertyChanged('sizingMethod', self.model.sizingMethod);
    };

    this.toggleSizingMethodAttributes = function () {
      self.form
        .find(Selectors.WIDGET.WRAPPERS.CAP_HEIGHT)
        .toggle(self.model.sizingMethod === SIZING_METHOD.CAP_HEIGHT);
      self.form
        .find(Selectors.WIDGET.WRAPPERS.WIDTH)
        .toggle(self.model.sizingMethod === SIZING_METHOD.WIDTH);
    };

    this.whenAlignmentChanged = function () {
      self.model.alignment = self.form
        .find(Selectors.WIDGET.INPUT.ALIGNMENT + ':checked')
        .val();
      self.triggerPropertyChanged('alignment', self.model.alignment);
      self.render();
    };

    this.whenMirroringChanged = function () {
      self.model.mirrored = translateBooleanSlug(
        self.form.find(Selectors.WIDGET.INPUT.MIRRORED + ':checked').val()
      );
      self.triggerPropertyChanged('mirrored', self.model.mirrored);
      self.render();
    };

    this.whenBgColorChanged = function () {
      // Uncheck the transparency checkbox when a background color is selected.
      self.form.find(Selectors.WIDGET.INPUT.TRANSPARENT).prop('checked', false);
      self.model.bgColor = self.form
        .find(Selectors.WIDGET.INPUT.BG_COLOR + ':checked')
        .val();
      self.triggerPropertyChanged('bgColor', self.model.bgColor);
      self.render();
    };

    this.whenFgColorChanged = function () {
      self.model.fgColor = self.form
        .find(Selectors.WIDGET.INPUT.FG_COLOR + ':checked')
        .val();
      self.triggerPropertyChanged('fgColor', self.model.fgColor);
      self.render();
    };

    this.whenFontChanged = function () {
      self.model.fontName = $(this).val();
      // Font changes impact the height, so re-render and update the height details.
      self.triggerPropertyChanged('fontName', self.model.fontName);
      self.render();
    };

    this.whenTransparencyChanged = function () {
      if ($(this).is(':checked')) {
        self.model.bgColor = 'transparent';
      } else {
        self.model.bgColor = self.form
          .find(Selectors.WIDGET.INPUT.BG_COLOR + ':checked')
          .val();
      }
      self.triggerPropertyChanged('bgColor', self.model.bgColor);
      self.render();
    };

    this.whenWidthChanged = function () {
      self.model.width = $(this).val();
      self.triggerPropertyChanged('width', self.model.width);
      self.render();
    };

    this.whenCapHeightChanged = function () {
      self.model.capHeight = $(this).val();
      self.triggerPropertyChanged('capHeight', self.model.capHeight);
      self.render();
    };

    this.setWidthRule = function (widthRule) {
      var select = self.form.find(Selectors.WIDGET.INPUT.WIDTH_RULES);
      select
        .val(select.find('option[value|="' + widthRule + '"]').attr('value'))
        .change();
    };

    this.renderPreviewImage = function (postRenderCallback) {
      self.form.find('button.single_add_to_cart_button').prop('disabled', true);
      var imageWidth = 1008;
      if (!self.canvas.is(':visible')) {
        imageWidth = 0;
      }
      var params = {
        text: $.trim(self.model.text),
        font_slug: self.model.fontName,
        image_width: imageWidth,
        fg_color: self.model.fgColor,
        bg_color: self.model.bgColor,
        alignment: self.model.alignment,
        mirrored: self.model.mirrored,
      };
      if (self.model.isSizingByCapHeight()) {
        params.desired_cap_height = self.model.capHeight;
      } else {
        params.desired_width = self.model.width;
      }
      var errorWrapper = self.form.find('.js-sticker-error');
      errorWrapper.hide();
      $.getJSON(
        '/wp-content/themes/stickers/customized/preview.php',
        params
      ).done(function (data) {
        if (data.status === 'ok') {
          self.form
            .find('button.single_add_to_cart_button')
            .prop('disabled', false);
          self.img.one('load', function () {
            var height = self.img.height();
            self.canvas.height(height);
            self.updateMarkers(data.markers, height, self.img.width());
          });
          self.img.attr('src', 'data:image/png;base64,' + data.image_data);
          self.setWidthRule(data.price_selection);
          var dimensions = data.dimensions;
          self.model.width = dimensions.width;
          self.triggerPropertyChanged('width', self.model.width);
          self.model.height = dimensions.height;
          self.triggerPropertyChanged('height', self.model.height);
          self.model.capHeight = dimensions.cap_height;
          self.triggerPropertyChanged('capHeight', self.model.capHeight);
          postRenderCallback && postRenderCallback();
        } else {
          errorWrapper.empty();
          var ul = $('<ul/>');
          $.each(data.errors, function (i, error) {
            ul.append($('<li/>').text(error));
          });
          errorWrapper.append(ul);
          errorWrapper.show();
          postRenderCallback && postRenderCallback();
        }
      });
    };

    this.updateMarkers = function (markers, height, width) {
      var textHeight = markers.bottom - markers.top;
      var textWidth = markers.right - markers.left;
      var offset = Math.ceil((height - textHeight) / 2);
      offset = Math.max(0, offset);
      $(Selectors.MARKERS.VERTICAL).css({
        top: offset + 10 + 'px',
        bottom: offset + 12 + 'px',
      }); // visually adjust the ruler with 10 & 12 px
      offset = Math.max(0, Math.ceil((width - textWidth) / 2));
      $(Selectors.MARKERS.HORIZONTAL).css({
        left: offset + 'px',
        right: offset + 'px',
      });
    };

    this.triggerPropertyChanged = function (property, value) {
      self.form.trigger('stickerPropertyChanged', [property, value]);
    };

    // Utility to wait for svg-inline to embed the svg from an <img>.
    // Pass a callback to be called as soon as we've got a SVG.
    this.waitForInlineSvg = function (cb) {
      var getSvg = function () {
        var element = self.form
          .closest('.summary')
          .prev('.images')
          .find('.wp-post-image');
        if (element.prop('tagName') === 'svg') {
          self.sampleElement = element;
          cb && cb();
        } else {
          setTimeout(getSvg, 200);
        }
      };
      getSvg();
    };

    // Handles rendering for an inline SVG.
    this.renderInlineSvg = function () {
      self.waitForInlineSvg(function () {
        self.sampleElement.css({
          fill: Stickers.colorMapping[self.model.fgColor],
        });
        self.sampleElement.toggleClass(
          'mirrored',
          self.model.mirrored === 'yes'
        );
      });
    };

    // Handles rendering for a regular image with a mask.
    this.renderImage = function () {
      self.sampleElement.css({
        backgroundColor: Stickers.colorMapping[self.model.fgColor],
      });
      self.sampleElement.toggleClass('mirrored', self.model.mirrored === 'yes');
    };
  };

  $(document).ready(function () {
    // WC PAO will list every choice you made. We don't need that but unfortunately there's no clean way to
    // override this behavior, which means we need to react whenever the addons are updated and remove those lines.
    $(document.body)
      .find('.cart:not(.cart_group)')
      .each(function () {
        var $cart = $(this);
        $cart.on('updated_addons', function () {
          $cart
            .find('.product-addon-totals')
            .find('li:not(.wc-pao-subtotal-line)')
            .remove();
        });
      });
    $('textarea.wc-pao-addon-custom-textarea').prop('rows', 2);
    new StickerSample({
      form: $('.js-sticker-form'),
      model: new StickerModel(),
    }).bootstrap();
  });
})(jQuery);
