document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('wp-pip-container');
  if (!container) return;

  const iframe = document.createElement('iframe');
  iframe.style.display = 'none';
  iframe.style.width = '100%';
  iframe.style.height = '100%';
  iframe.style.border = 'none';

  const button = document.createElement('button');
  button.textContent = wpPipData.pipButtonText;
  button.className = 'button button_pip';

  const errorSpan = document.createElement('span');
  errorSpan.className = 'pip-error';

  container.appendChild(button);
  container.appendChild(errorSpan);

  let isInPiPMode = false;
  const isPiPSupported = 'documentPictureInPicture' in window;

  button.disabled = !isPiPSupported;

  if (!isPiPSupported) {
    errorSpan.textContent = wpPipData.unsupportedBrowserText;
  }

  async function loadIframe() {
    return new Promise((resolve, reject) => {
      iframe.onload = resolve;
      iframe.onerror = reject;
      iframe.src = wpPipData.previewLink;
    });
  }

  async function togglePiP() {
    try {
      if (!document.pictureInPictureElement) {
        await loadIframe();
        const pipWindow = await documentPictureInPicture.requestWindow({
          width: 390,
          height: 844
        });
        const styleSheet = 'body { margin: 0; }';
        const style = document.createElement('style');
        style.textContent = styleSheet;
        pipWindow.document.head.appendChild(style);

        pipWindow.document.body.appendChild(iframe);
        isInPiPMode = true;
      } else {
        await document.exitPictureInPicture();
        container.appendChild(iframe);
        isInPiPMode = false;
      }
    } catch (error) {
      console.error('PiP error:', error);
      errorSpan.textContent = wpPipData.unsupportedBrowserText;
    }
  }

  button.addEventListener('click', function (event) {
    event.preventDefault();
    togglePiP();
  });

  document.addEventListener('picture-in-picture-change', (event) => {
    isInPiPMode = event.target === iframe;
  });
});