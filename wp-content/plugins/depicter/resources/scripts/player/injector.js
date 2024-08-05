/**
 * It's a standalone module that injects given data to the player markup right before the player initialization
 * This module can be used to show a preview of a template on realtime with the user's given data
 */

/**
 * Fill the image based on type on box
 * @param {String} type type of fit cover or contain
 * @param {number} boxW box width
 * @param {number} boxH box height
 * @param {number} width media width
 * @param {number} height media width
 */
const fitToBox = (type, boxW, boxH, width, height) => {
   const wr = boxW / width;
   const hr = boxH / height;
   const ratio = type === 'cover' ? Math.max(wr, hr) : Math.min(wr, hr);

   return {
      width: width * ratio,
      height: height * ratio,
   };
};

const parseJSONAttr = (value) => {
   try {
      return JSON.parse(value.replace(/'/g, '"'));
   } catch (e) {
      console.warn('Given data value is not a valid JSON, skipped. \n ' + value);
   }

   return null;
};

const calcCropSize = (size, element) => {
   // has crop data
   if (element.dataset.crop) {
      const cropData = parseJSONAttr(element.dataset.crop);
      const {
         mediaSize: { width: mw, height: mh },
      } = cropData;
      const { width, height } = size;
      cropData.mediaSize = fitToBox('cover', mw, mh, width, height);
      element.dataset.crop = JSON.stringify(cropData).replace('"', "'");
   }
};

const replaceImg = (src, size, element) => {
   calcCropSize(size, element);
   delete element.dataset.depicterSrcset;
   element.dataset.depicterSrc = src;

   delete element.dataset.srcset;
   element.dataset.src = src;
};

const replacePicture = (src, size, element) => {
   calcCropSize(size, element);
   const img = element.querySelector('img');
   delete img.dataset.depicterSrcset;
   img.dataset.depicterSrc = src;
   element.querySelectorAll('source')?.forEach((el) => {
      el.remove();
   });
};

const replaceImages = (pattern, targetElement) => {
   Object.entries(pattern).forEach(([className, { src, size }]) => {
      targetElement.querySelectorAll('.' + className)?.forEach((element) => {
         if (element.tagName === 'IMG') {
            replaceImg(src, size, element);
         } else if (element.tagName === 'PICTURE') {
            replacePicture(src, size, element);
         } else if (element.tagName === 'DIV' || element.classList.contains('depicter-section')) {
            const targetEl = element.querySelector('img.depicter-bg');
            if (targetEl) replaceImg(src, size, targetEl);
         }
      });
   });
};

const replaceContent = (pattern, targetElement) => {
   Object.entries(pattern).forEach(([className, content]) => {
      targetElement.querySelectorAll('.' + className)?.forEach((element) => {
         element.innerHTML = content;
      });
   });
};

const replaceColors = (pattern, targetElement = document.documentElement) => {
   Object.entries(pattern).forEach(([color, value]) => {
      targetElement.style.setProperty(color, value);
   });
};

window.depicterInjector = (target) => {
   const targetElement = document.querySelector(target);
   window.addEventListener('message', (event) => {
      const { data } = event;

      switch (data.action) {
         case 'replaceContent':
            replaceContent(data.pattern, targetElement);
            break;
         case 'replaceImages':
            replaceImages(data.pattern, targetElement);
            break;
         case 'replaceColors':
            replaceColors(data.pattern);
            break;
         case 'init':
            window.Depicter.initAll();
            break;
         default:
            break;
      }
   });
};

// disable auto Depicter init
window.DepicterDisableAutoInit = true;
