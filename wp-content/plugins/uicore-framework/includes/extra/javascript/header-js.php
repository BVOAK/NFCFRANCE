<?php
defined('ABSPATH') || exit();

//INCLUDED IN CLASS JS
if($settings['menu_interaction'] === 'focus'){
  $js .= "
  document.body.classList.add('uicore-menu-focus');
  ";
}elseif($settings['menu_interaction'] === 'text flip'){
    $js .= "
    if(window.innerWidth >= ".$settings["mobile_breakpoint"]."){
        document.querySelectorAll('.uicore-menu li').forEach(function(li) {
            li.addEventListener('mouseenter', function(e) {
                var animClass = 'ui-anim-flip';
                if (!li.classList.contains(animClass)) {
                    var a = li.querySelector('a');
                    if (!a) return;
                    var btnContent = a.querySelector('.ui-menu-item-wrapper');
                    if (!btnContent) return;

                    // Clone and insert after
                    var clone = btnContent.cloneNode(true);
                    btnContent.parentNode.insertBefore(clone, btnContent.nextSibling);

                    // Wrap all .ui-menu-item-wrapper in a new div
                    var wrappers = a.querySelectorAll('.ui-menu-item-wrapper');
                    var wrapDiv = document.createElement('div');
                    wrapDiv.className = 'ui-flip-anim-wrapp';
                    wrappers.forEach(function(w) {
                        wrapDiv.appendChild(w);
                    });
                    a.appendChild(wrapDiv);

                    setTimeout(function() {
                        li.classList.add(animClass);
                    }, 10);
                }
            });
        });
    }
    ";
}elseif($settings['menu_interaction'] === 'magnet button'){
    $js .= '
    if(window.innerWidth >= '.$settings["mobile_breakpoint"].'){
        const nav = document.querySelector("ul.uicore-menu");
        const menu = document.querySelector("#wrapper-navbar .uicore-header-wrapper");
        const ctaBtn = document.querySelector(".uicore-cta-wrapper a");
        let customTop = ctaBtn ? ctaBtn.getBoundingClientRect().top : 0;
        let customHeight = ctaBtn ? ctaBtn.offsetHeight : 0;
        let isPilCompact = false;
    ';
    if (in_array($settings['header_pill'], ['menu', 'logo-menu', 'true'])) {
        $js.='
            const heightFallback = (nav.offsetHeight - 8) + "px";
            const topFallback = (nav.getBoundingClientRect().top + window.scrollY - window.scrollY + 4) + "px";
            const isPill = true;
            ';
    }else if($settings['header_pill'] === 'compact'){
        $js.='
            const heightFallback = "2.4rem";
            const topFallback = "calc(calc(var(--uicore-header--menu-typo-h) / 2) - 1.2rem)";
            const isPill = false;
            customTop = customTop - (customHeight / 2) + 2;
            isPilCompact = true;
            ';
    }else{
        $js.='
            const heightFallback = "2.4rem";
            const topFallback = "calc(calc(var(--uicore-header--menu-typo-h) / 2) - 1.2rem)";
            const isPill = false;
            ';
    }
    // - menu.getBoundingClientRect().top)
    $js .= '
        const fakeBtnHeight = ctaBtn ? customHeight + "px" : heightFallback;
        const fakeBtnTop = ctaBtn ? customTop + "px" : topFallback; 
        const fakeBtnRadius = ctaBtn ? window.getComputedStyle(ctaBtn).borderRadius : "var(--ui-radius)";

        const updateActiveItemStyles = (anchor) => {
            const isLastChild = anchor.parentElement === anchor.parentElement.parentElement.lastElementChild;
            const anchorBounds = anchor.getBoundingClientRect();
            const navBounds = isPill ? 0 : menu.getBoundingClientRect().left;
            const relativeLeft = anchorBounds.left - navBounds;
            let width = anchorBounds.width +"px";
            if(!isPill && !isPilCompact && isLastChild){
                width = "calc("+anchorBounds.width+"px + "+window.getComputedStyle(anchor).paddingLeft+")";
            }

            nav.style.setProperty("--item-active-x", `${relativeLeft}px`);
            nav.style.setProperty("--item-active-width", width);
        };

        nav.querySelectorAll("li > a").forEach(function(a) {
            a.addEventListener("pointerenter", function() {
                updateActiveItemStyles(this);
            });
        });

        const deactivate = async () => {
            const transitions = nav.getAnimations();
            if (transitions.length) {
                const fade = transitions.find(t => t.effect.target === nav.firstElementChild && t.transitionProperty === "opacity");
                await Promise.allSettled([fade.finished]);
                nav.style.setProperty("--item-active-x", "");
                nav.style.setProperty("--item-active-width", "");
            }
        };

        nav.addEventListener("pointerleave", deactivate);
        nav.addEventListener("blur", deactivate);
        nav.style.setProperty("--item-active-height", `${fakeBtnHeight}`);
        nav.style.setProperty("--item-active-y", `${fakeBtnTop}`);
        nav.style.setProperty("--item-active-radius", `${fakeBtnRadius}`);
    }
    ';
    //if(window.innerWidth >= '.$settings["mobile_breakpoint"].'){ }
}
if($settings['submenu_trigger'] === 'click' && strpos($settings['header_layout'], 'ham') == false){
  $js .= "
  if (window.innerWidth >= ".$settings["mobile_breakpoint"].") {
    document.querySelectorAll('.uicore-nav .menu-item-has-children').forEach(function(menuItem) {
        menuItem.addEventListener('click', function(e) {
            const target = e.target;
            const parentLi = target.closest('li');
            if (
                (target.tagName === 'LI' ||
                    (target.tagName === 'A' && target.parentElement && target.parentElement.tagName === 'LI') ||
                    target.classList.contains('ui-menu-item-wrapper')) &&
                (parentLi && parentLi.classList.contains('menu-item-has-children') &&
                    (!target.classList.length || (target.classList.length === 1 && target.classList.contains('ui-menu-item-wrapper'))))
            ) {
                e.preventDefault();
                e.stopPropagation();
            }
            const sub = menuItem.querySelector(':scope > .sub-menu');

            // Hide other open sub-menus
            const siblings = Array.from(menuItem.parentElement.children).filter(
                el => el !== menuItem
            );
            siblings.forEach(function(sibling) {
                const siblingSub = sibling.querySelector(':scope > .sub-menu');
                if (siblingSub) siblingSub.style.display = 'none';
                sibling.querySelectorAll('.sub-menu').forEach(function(subMenu) {
                    subMenu.style.display = 'none';
                });
            });

            if (sub && sub.style.display === '' || sub && sub.style.display === 'none') {
                sub.style.display = 'flex';
                sub.style.opacity = 1;
                sub.style.transform = 'none';
                sub.style.pointerEvents = 'all';
            } else if (sub) {
                sub.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function(e) {
        const clickedElement = e.target;
        if (!clickedElement.closest('.sub-menu')) {
            document.querySelectorAll('.sub-menu').forEach(function(subMenu) {
                subMenu.style.display = 'none';
            });
        }
    });
  }
  ";
}
if($settings['animations_submenu'] === 'scale bg'){
  $js .= "
  document.addEventListener('DOMContentLoaded', function() {
    let timeout;
    document.querySelectorAll('.uicore-menu li.menu-item-has-children').forEach(function(li) {
        li.addEventListener('mouseenter', function() {
            const subMenu = li.querySelector('.sub-menu');
            let distanceFromTop = 0;
            let topOffset = 0;
            if (subMenu) {
                const subMenuRect = subMenu.getBoundingClientRect();
                const liRect = li.getBoundingClientRect();
                distanceFromTop = subMenuRect.top + subMenuRect.height - 10;
                topOffset = liRect.top;
            }
            clearTimeout(timeout);
            document.querySelectorAll('.uicore-header-wrapper').forEach(function(header) {
                header.style.setProperty('--ui-bg-height', (distanceFromTop - topOffset) + 'px');
            });
            const menu = document.querySelector('.uicore-transparent');
            if (menu) {
                menu.classList.add('uicore-transparent-color');
            }
        });
        li.addEventListener('mouseleave', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                document.querySelectorAll('.uicore-header-wrapper').forEach(function(header) {
                    header.style.setProperty('--ui-bg-height', '100%');
                });
                setTimeout(() => {
                    document.querySelectorAll('.uicore-transparent-color').forEach(function(el) {
                        el.classList.remove('uicore-transparent-color');
                    });
                }, 70);
            }, 65);
        });
    });
});
  ";
}
if($settings['animations_submenu'] === 'website blur'){
  $js .= "
    const content = document.getElementById('content');
    if (content) {
        content.style.transition = 'all 0.3s cubic-bezier(.33,1,.68,1)';
        content.style.willChange = 'filter transform';
    }

    let removeBlurTimeout;
    document.querySelectorAll('.uicore-menu li.menu-item-has-children').forEach(function(li) {
        li.addEventListener('mouseenter', function() {
            clearTimeout(removeBlurTimeout);
            document.body.classList.add('uicore-blur-on');
        });
        li.addEventListener('mouseleave', function() {
            removeBlurTimeout = setTimeout(() => {
                document.body.classList.remove('uicore-blur-on');
            }, 70);
        });
    });
  ";
}

if($settings['mmenu_animation'] === 'expand'){
  $js .= "
    window.uicoreBeforeMobileMenuShow = function() {
        const wrapper = document.querySelector('.uicore-mobile-menu-wrapper');
        const nav = wrapper ? wrapper.querySelector('nav') : null;
        const activeUls = wrapper ? wrapper.querySelectorAll('ul.uicore-active') : [];
        const lastActiveUl = activeUls.length ? activeUls[activeUls.length - 1] : null;
        const extra = wrapper ? wrapper.querySelector('.uicore-extra') : null;

        const navHeight = nav ? nav.offsetHeight : 0;
        const lastUlHeight = lastActiveUl ? lastActiveUl.offsetHeight : 0;
        const extraHeight = extra ? extra.offsetHeight : 0;

        const height = navHeight + lastUlHeight + extraHeight + 30;
        const heightCalc = 'calc(' + height + 'px + 2em)';
        if (wrapper) {
            wrapper.style.maxHeight = heightCalc;
        }
    };

    window.uicoreBeforeMobileMenuHide = function() {
        const wrapper = document.querySelector('.uicore-mobile-menu-wrapper');
        if (wrapper) {
            wrapper.style.maxHeight = '0';
        }
    };
  ";
}