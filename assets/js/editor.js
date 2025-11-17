(function (wp) {
  if (!wp || !wp.blocks || !wp.domReady) {
    return;
  }

  const { registerBlockVariation } = wp.blocks;
  const { __ } = wp.i18n;

  wp.domReady(function () {
    if (typeof registerBlockVariation !== 'function') {
      return;
    }

    registerBlockVariation('core/media-text', {
      name: 'yourtheme-link-card',
      title: __('لینک دهی', 'yourtheme'),
      description: __('بلوک ویژه برای نمایش تصویر، متن لینک و دکمه اقدام.', 'yourtheme'),
      scope: ['inserter'],
      attributes: {
        className: 'yt-link-card',
        mediaPosition: 'left',
        mediaType: 'image',
        verticalAlignment: 'center',
        isStackedOnMobile: false
      },
      innerBlocks: [
        [
          'core/paragraph',
          {
            placeholder: __('متن لینک را اینجا وارد کنید…', 'yourtheme'),
            className: 'yt-link-card__text'
          }
        ],
        [
          'core/buttons',
          {
            layout: { type: 'flex', justifyContent: 'flex-start' }
          },
          [
            [
              'core/button',
              {
                text: __('کلیک کنید', 'yourtheme'),
                className: 'yt-link-card__button'
              }
            ]
          ]
        ]
      ],
      example: {
        attributes: {
          className: 'yt-link-card',
          mediaPosition: 'left',
          verticalAlignment: 'center',
          mediaUrl: 'https://via.placeholder.com/160'
        },
        innerBlocks: [
          {
            name: 'core/paragraph',
            attributes: {
              className: 'yt-link-card__text',
              content: 'https://example.com/demo-article'
            }
          },
          {
            name: 'core/buttons',
            innerBlocks: [
              {
                name: 'core/button',
                attributes: {
                  text: __('کلیک کنید', 'yourtheme')
                }
              }
            ]
          }
        ]
      }
    });
  });
})(window.wp);
