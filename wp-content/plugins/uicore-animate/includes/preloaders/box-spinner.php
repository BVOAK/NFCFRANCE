<style>
    .ui-anim-box {
        width: 75px;
        height: 75px;
        border-radius: 3px;
        background-color: var(--ui-e-anim-preloader-color);
        animation: uiAnimLoader 500ms linear infinite;
    }

    @keyframes uiAnimLoader {
        17% {
            border-bottom-right-radius: 3px;
        }

        25% {
            transform: translateY(9px) rotate(22.5deg);
        }

        50% {
            border-bottom-right-radius: 40px;
            transform: translateY(21px) scale(1, .9) rotate(45deg);
        }

        75% {
            transform: translateY(9px) rotate(67.5deg);
        }

        100% {
            transform: translateY(0) rotate(90deg);
        }
    }

    .ui-anim-box-shadow {
        width: 75px;
        height: 7.5px;
        margin-top: 17px;
        border-radius: 50%;
        background-color: #000000;
        opacity: 0.1;
        animation: uiAnimLoader-shadow 500ms linear infinite;
    }

    @keyframes uiAnimLoader-shadow {
        50% {
            transform: scale(1.2, 1);
        }
    }
</style>

<div class="ui-anim-box"></div>
<div class="ui-anim-box-shadow"></div>