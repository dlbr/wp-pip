<?php 

/**
 * Plugin Name: DLBR (Picture-in-Picture Preview)
 * Plugin URI: https://dlbr.dev
 * Description: Picture-in-Picture (PiP) allows you to preview changes in a floating window (always on top of other windows)
 * Version: 1.0.0
 * Author: Dlbr
 * Author URI: https://dlbr.dev
 */

if (!defined('ABSPATH')) {
    exit; 
}

class WP_PiP {
  public function __construct() {
    add_action('post_submitbox_minor_actions', array($this, 'add_pip'), 11);
  }

  public function add_pip() {
    global $post;
    if (class_exists('WooCommerce')) {
      if (function_exists('is_product') && is_product()) {
        $product = wc_get_product($post->ID);
        $preview_link = $product->get_permalink();
      }  else {
        if ($post->post_status === 'publish') {
          $preview_link = get_permalink($post->ID);
        } else {
          $preview_link = get_preview_post_link($post);
        }
      }
    }
  ?>
  <script type="importmap">
    {
      "imports": {
        "vue": "https://unpkg.com/vue@3.5.4/dist/vue.esm-browser.js"
      }
    }
  </script>
  <script type="module">
    import { ref, createApp, onMounted, onBeforeUnmount } from 'vue'
    const app = createApp({ 
      setup() {
        const iframe = ref(null)
        const isInPiPMode = ref(false)
        const isPiPSupported = ref(false)
        const pipMode = (state = false) => isInPiPMode.value = state

        const togglePiP = async () => {
          if (!document.pictureInPictureElement) {
            const pipWindow = await documentPictureInPicture.requestWindow({
              width: 390,
              height: 844
            })
            const styleSheet = 'iframe { display: block; width: 100%; height: 100%; margin: 0;} body {margin: 0;}'
            const style = document.createElement('style')
            style.textContent = styleSheet
            pipWindow.document.head.appendChild(style)

            pipWindow.document.body.append(iframe.value)
            isInPiPMode.value = true
          } else {
            await document.exitPictureInPicture()
            isInPiPMode.value = false
          }
        }

        onMounted(() => {
          isPiPSupported.value = ('pictureInPictureEnabled' in document)
          iframe.value.addEventListener('enterpictureinpicture', pipMode(true))
          iframe.value.addEventListener('leavepictureinpicture', pipMode())
        })
        onBeforeUnmount(() => {
          iframe.value.removeEventListener('enterpictureinpicture', pipMode())
          iframe.value.removeEventListener('leavepictureinpicture', pipMode())
        })
        return {
          iframe,
          isInPiPMode,
          isPiPSupported,
          togglePiP
        }
      },
      template: `<iframe hidden ref="iframe"  src="<?php echo esc_url($preview_link); ?>" frameBorder="0" />
      <?php echo '<button @click.prevent="togglePiP"  class="button button_pip" :disabled="!isPiPSupported">PiP</button>'; ?>`
    })
    document.addEventListener('DOMContentLoaded', () => app.mount('#app'));
  </script>
  <style>.button_pip { margin-right: 8px!important}</style>
  <div id="app"></div>
  <?php }
}

new WP_PiP();