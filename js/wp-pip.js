document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('wp-pip-container');
  if (!container) return;

  const iframe = document.createElement('iframe');
  iframe.src = wpPipData.previewLink;
  iframe.style.display = 'none';
  iframe.setAttribute('frameBorder', '0');

  const button = document.createElement('button');
  button.textContent = wpPipData.pipButtonText;
  button.className = 'button button_pip';

  const errorSpan = document.createElement('span');
  errorSpan.className = 'pip-error';

  container.appendChild(iframe);
  container.appendChild(button);
  container.appendChild(errorSpan);

  let isInPiPMode = false;
  const isPiPSupported = 'pictureInPictureEnabled' in document;

  button.disabled = !isPiPSupported;

  if (!isPiPSupported) {
    errorSpan.textContent = wpPipData.unsupportedBrowserText;
  }

  async function togglePiP() {
    try {
      if (!document.pictureInPictureElement) {
        const pipWindow = await documentPictureInPicture.requestWindow({
          width: 390,
          height: 844
        });
        const styleSheet = 'iframe { display: block; width: 100%; height: 100%; margin: 0;} body {margin: 0;}';
        const style = document.createElement('style');
        style.textContent = styleSheet;
        pipWindow.document.head.appendChild(style);

        pipWindow.document.body.appendChild(iframe);
        isInPiPMode = true;
      } else {
        await document.exitPictureInPicture();
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

  iframe.addEventListener('enterpictureinpicture', function () {
    isInPiPMode = true;
  });

  iframe.addEventListener('leavepictureinpicture', function () {
    isInPiPMode = false;
  });
});