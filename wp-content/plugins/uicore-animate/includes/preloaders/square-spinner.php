<style>
    .ui-anim-spinner {
        width: 70px;
        height: 70px;
        display: inline-block;
        position: relative;
    }

    .ui-anim-spinner::after,
    .ui-anim-spinner::before {
        content: '';
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 2px solid var(--ui-e-anim-preloader-color);
        position: absolute;
        left: 0;
        top: 0;
        animation: uiAnimLoader 2s linear infinite;
    }

    .ui-anim-spinner::after {
        animation-delay: 1s;
    }

    @keyframes uiAnimLoader {
        0% {
            transform: scale(0);
            opacity: 1;
        }

        100% {
            transform: scale(1);
            opacity: 0;
        }
    }
</style>
<div class="ui-anim-spinner"></div>