<style>
    .ui-anim-preloader {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        width: 100vw;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999999999999999999999999999;
        transition: opacity .4s ease;
    }

    .ui-anim-preloader-mask {
        overflow: hidden;
    }

    .ui-anim-preloader-text {
        font-size: 2.5rem;
        line-height: 3.3rem;
        font-weight: 700;
        color: var(--ui-e-anim-preloader-color);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        transform: translateY(3.5rem);
        transition: opacity .4s ease, transform .4s ease;
    }
</style>
<div class="ui-anim-preloader">
    <?php
    $words = \UiCoreAnimate\Settings::get_option('animations_preloader_words');
    $words = explode('|', $words);
    //generate markup
    foreach ($words as $key => $word) {
        echo '<div class="ui-anim-preloader-mask"><div class="ui-anim-preloader-text">' .  $word . '</div></div>&nbsp;';
        if ($key < count($words) - 1) {
            echo '&nbsp;';
        }
    }

    ?>
</div>

<script>
    const uiAnimPreloaderTexts = document.querySelectorAll('.ui-anim-preloader-text');
    for (let i = 0; i < uiAnimPreloaderTexts.length; i++) {
        setTimeout(() => {
            uiAnimPreloaderTexts[i].style.transform = 'translateY(0)';

            if (i === uiAnimPreloaderTexts.length - 1) {
                setTimeout(() => {
                    uiAnimateCustomPreloaderHide();
                }, 1000);
            }
        }, i * 300);
    }
    uiAnimateCustomPreloaderHide = () => {

        <?php if ($this->animation !== 'none') : ?>
            uiAnimatePreloaderHide();
        <?php endif; ?>

        document.querySelector('.ui-anim-preloader').style.opacity = 0;
        setTimeout(() => {
            document.querySelector('.ui-anim-preloader').style.display = 'none';
        }, 500);
    }
    window.uiAnimateCustomPreloaderShow = true;
</script>