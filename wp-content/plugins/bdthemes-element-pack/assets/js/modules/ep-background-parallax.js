;(function ($) {
  var selector = "[class^='mdw-turbulence-effect'], [class*=' mdw-turbulence-effect']",
      image = [],
      img = [],
      windowHeight,
      windowWidth,
      imgWidth = [],
      imgHeight = [],
      imgOffset = [],
      maxRadius = [],
      type = [],
      brightness = { start: 0.8, end: 1.0 },
      scale = { start: 0.9, end: 1.0 },
      isSafari,
      fallbackCircle,
      previousWidth;

  function getValue(el, prop) {
    return getComputedStyle(el[0]).getPropertyValue(prop);
  }

  function init() {

    isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    fallbackCircle = getValue($('body'), '--fallback-circle') === 'true';

    $('body').append('<div class="mdw-100vh" style="height: 100vh; display: none;"></div>');

    $(selector).each(function (i) {
      var $this = $(this);
      image[i] = $this.find('.elementor-widget-image');
      img[i] = image[i].find('img');
    });
  }

  function setValues() {
    windowHeight = $('.mdw-100vh').height();
    windowWidth = $(window).width();

    $(selector).each(function (i) {
      imgWidth[i] = img[i].width();
      imgHeight[i] = img[i].height();
      maxRadius[i] = Math.sqrt(Math.pow(imgWidth[i] / 2, 2) + Math.pow(imgHeight[i] / 2, 2)) + 10;

      if (isSafari && !fallbackCircle && maxRadius[i] > 850) {
        maxRadius[i] = 850;
      }
    });
  }

  function setSVG() {
    $(selector).each(function (i) {
      var $this = $(this),
          imgContainer = image[i].find('.elementor-widget-container'),
          imgNaturalWidth = img[i][0]?.naturalWidth || 0,
          imgNaturalHeight = img[i][0]?.naturalHeight || 0,
          imgSrcset = img[i].attr('srcset')?.split(' ') || [],
          imgUrl = imgSrcset[imgSrcset.length - 2] || img[i].attr('src'),
          className = $this.attr('class'),
          shortClass = (className.match(/mdw-turbulence-effect\S*/) || [])[0] || '',
          values = shortClass.split('-'),
          blurHTML = shortClass.includes('blur') && windowWidth >= 768 && !isSafari 
                     ? '<feGaussianBlur in="displacement" stdDeviation="10"></feGaussianBlur>' 
                     : '',
          shapeHTML = `<circle cx="50%" cy="50%" fill="white" class="mask" style="filter: url(#MDWFilter${i + 1});"></circle>`,
          effectResolution = 0.03,
          effectArea = 50,
          effectOctave = 3,
          effectHTML,
          svgHTML;

      values.forEach((value, index) => {
        if (value === 'resolution' && !isNaN(values[index + 1])) {
          effectResolution = parseFloat(values[index + 1]) * 0.003;
        }
        if (value === 'area' && !isNaN(values[index + 1])) {
          effectArea = parseFloat(values[index + 1]) * 5;
        }
      });

      if (effectArea > 100) effectOctave = 1;

      if (blurHTML) {
        image[i].addClass('blur');
        effectResolution = 0.01;
        effectArea = 150;
        effectOctave = 3;
      }

      type[i] = shortClass.includes('eye') ? 'eye' : 'circle';

      if (type[i] === 'eye') {
        image[i].addClass('eye');
        effectResolution = blurHTML ? 0 : 0.06;
        effectArea = 50;
        shapeHTML = `<path d="M 0 ${imgHeight[i] / 2} Q ${imgWidth[i] / 2} ${
          (3 * imgHeight[i]) / 2 - 2 * 12
        } ${imgWidth[i]} ${imgHeight[i] / 2} Q ${imgWidth[i] / 2} ${
          2 * 12 - imgHeight[i] / 2
        } 0 ${imgHeight[i] / 2}" fill="white" class="mask" style="filter: url(#MDWFilter${i + 1});"></path>`;
      }

      effectHTML = fallbackCircle && (windowWidth < 768 || isSafari)
        ? ''
        : `<defs>
             <filter id="MDWFilter${i + 1}">
               <feTurbulence type="fractalNoise" baseFrequency="${effectResolution}" numOctaves="${effectOctave}" result="noise"></feTurbulence>
               <feDisplacementMap in="SourceGraphic" in2="noise" scale="${effectArea}" xChannelSelector="R" yChannelSelector="G"></feDisplacementMap>
               ${blurHTML}
             </filter>
             <mask id="MDWCircle${i + 1}">
               ${shapeHTML}
             </mask>
           </defs>`;

      svgHTML = `<svg width="${imgWidth[i]}" height="${imgHeight[i]}" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 ${imgWidth[i]} ${imgHeight[i]}">
                  ${effectHTML}
                  <g mask="url(#MDWCircle${i + 1})">
                    <image href="${imgUrl}" width="${imgWidth[i]}" height="${imgHeight[i]}" style="transform: scale(${scale.start}); transform-origin: center center; filter: brightness(${brightness.start});" preserveAspectRatio="xMidYMid slice"></image>
                  </g>
                </svg>`;

      imgContainer.find('svg').remove();
      imgContainer.append(svgHTML);
    });
  }

  function revealImage() {
    var imgStartPercent = 90,
        imgEndPercent = 60;

    $(selector).each(function (i) {
      imgOffset[i] = img[i][0].getBoundingClientRect();

      var svg = image[i].find('svg'),
          revealAmount = getScrollValue(imgOffset[i], imgHeight[i], imgStartPercent, imgEndPercent),
          currentBrightness = brightness.start + (brightness.end - brightness.start) * revealAmount,
          currentScale = scale.start + (scale.end - scale.start) * revealAmount,
          currentRadius = maxRadius[i] * revealAmount;

      svg.find('image').css({
        filter: `brightness(${currentBrightness})`,
        transform: `scale(${currentScale})`,
      });

      if (fallbackCircle && (windowWidth < 768 || isSafari)) {
        svg.css('clip-path', type[i] === 'circle'
          ? `circle(${currentRadius}px at 50% 50%)`
          : `ellipse(50% ${revealAmount * 50}% at 50% 50%)`);
      } else {
        if (type[i] === 'circle') {
          svg.find('circle').attr('r', currentRadius);
        } else {
          svg.find('path').attr(
            'd',
            `M 0 ${imgHeight[i] / 2} Q ${imgWidth[i] / 2} ${
              imgHeight[i] / 2 + (imgHeight[i] - 24) * revealAmount
            } ${imgWidth[i]} ${imgHeight[i] / 2} Q ${imgWidth[i] / 2} ${
              imgHeight[i] / 2 - (imgHeight[i] - 24) * revealAmount
            } 0 ${imgHeight[i] / 2}`
          );
        }
      }
    });
  }

  function getScrollValue(imgOffset, imgHeight, startPercent, endPercent, inverse = false) {
    var start = (startPercent / 100) * windowHeight,
        end = (endPercent / 100) * windowHeight,
        scrollValue = (imgOffset.top + imgHeight / 2 - start) / (end - start),
        value = Math.max(Math.min(scrollValue, 1), 0);

    return inverse ? 1 - value : value;
  }

  function runAnimation(e) {
    if (e.type === 'load') init();
    setValues();
    setSVG();
    revealImage();
  }

  $(document).ready();
  $(window).on('scroll', revealImage);
  $(window).on('load resize', runAnimation);
})(jQuery);