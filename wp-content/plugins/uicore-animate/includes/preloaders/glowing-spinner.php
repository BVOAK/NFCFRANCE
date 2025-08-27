<style>
    .ui-anim-spinner {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        /* max-width: 14.6rem; */
        margin-top: 50px;
        margin-bottom: 50px;
    }

    .ui-anim-spinner:before,
    .ui-anim-spinner:after {
        content: "";
        position: absolute;
        border-radius: 50%;
        animation-duration: 1.8s;
        animation-iteration-count: infinite;
        animation-timing-function: ease-in-out;
        filter: drop-shadow(0 0 0.7555555556rem var(--ui-e-anim-preloader-color));
    }

    .ui-anim-spinner:before {
        width: 100%;
        padding-bottom: 100%;
        box-shadow: inset 0 0 0 .7rem var(--ui-e-anim-preloader-color);
        animation-name: uiAnimLoaderA;
    }

    .ui-anim-spinner:after {
        width: calc(100% - .7rem*2);
        padding-bottom: calc(100% - .7rem*2);
        box-shadow: 0 0 0 0 var(--ui-e-anim-preloader-color);
        animation-name: uiAnimLoaderB;
    }

    @keyframes uiAnimLoaderA {
        0% {
            box-shadow: inset 0 0 0 .7rem var(--ui-e-anim-preloader-color);
            opacity: 1;
        }

        50%,
        100% {
            box-shadow: inset 0 0 0 0 var(--ui-e-anim-preloader-color);
            opacity: 0;
        }
    }

    @keyframes uiAnimLoaderB {

        0%,
        50% {
            box-shadow: 0 0 0 0 var(--ui-e-anim-preloader-color);
            opacity: 0;
        }

        100% {
            box-shadow: 0 0 0 .7rem var(--ui-e-anim-preloader-color);
            opacity: 1;
        }
    }
</style>
<div class="ui-anim-spinner"></div>