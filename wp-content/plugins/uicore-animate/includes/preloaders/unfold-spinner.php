<style>
    .ui-anim-spinner {
        width: 70px;
        height: 70px;
        display: inline-block;
        position: relative;
        transform: rotate(45deg);
    }

    .ui-anim-spinner::before {
        content: '';
        width: 35px;

        height: 35px;

        position: absolute;
        left: 0;
        top: -35px;
        animation: uiAnimLoader 4s ease infinite;
    }

    .ui-anim-spinner::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 35px;
        height: 35px;
        background: var(--ui-e-anim-preloader-color);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        animation: uiAnimLoader2 2s ease infinite;
    }

    @keyframes uiAnimLoader2 {
        0% {
            transform: translate(0, 0) rotateX(0) rotateY(0)
        }

        25% {
            transform: translate(100%, 0) rotateX(0) rotateY(180deg)
        }

        50% {
            transform: translate(100%, 100%) rotateX(-180deg) rotateY(180deg)
        }

        75% {
            transform: translate(0, 100%) rotateX(-180deg) rotateY(360deg)
        }

        100% {
            transform: translate(0, 0) rotateX(0) rotateY(360deg)
        }
    }

    @keyframes uiAnimLoader {
        0% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 48px rgba(var(--ui-e-anim-preloader-color), 0), 0px 48px rgba(var(--ui-e-anim-preloader-color), 0)
        }

        12% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 48px rgba(var(--ui-e-anim-preloader-color), 0), 0px 48px rgba(var(--ui-e-anim-preloader-color), 0)
        }

        25% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 48px rgba(var(--ui-e-anim-preloader-color), 0), 0px 48px rgba(var(--ui-e-anim-preloader-color), 0)
        }

        37% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 48px rgba(var(--ui-e-anim-preloader-color), 1), 0px 48px rgba(var(--ui-e-anim-preloader-color), 0)
        }

        50% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 48px rgba(var(--ui-e-anim-preloader-color), 1), 0px 48px rgba(var(--ui-e-anim-preloader-color), 1)
        }

        62% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 24px rgba(var(--ui-e-anim-preloader-color), 1), 24px 48px rgba(var(--ui-e-anim-preloader-color), 1), 0px 48px rgba(var(--ui-e-anim-preloader-color), 1)
        }

        75% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 48px rgba(var(--ui-e-anim-preloader-color), 1), 0px 48px rgba(var(--ui-e-anim-preloader-color), 1)
        }

        87% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 48px rgba(var(--ui-e-anim-preloader-color), 0), 0px 48px rgba(var(--ui-e-anim-preloader-color), 1)
        }

        100% {
            box-shadow: 0 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 24px rgba(var(--ui-e-anim-preloader-color), 0), 24px 48px rgba(var(--ui-e-anim-preloader-color), 0), 0px 48px rgba(var(--ui-e-anim-preloader-color), 0)
        }
    }
</style>
<div class="ui-anim-spinner"></div>