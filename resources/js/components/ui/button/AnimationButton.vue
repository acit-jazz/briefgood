<template>
  <div class="bubble-wrapper">
    <span class="button--bubble__container">
      <a
        :href="href"
        class="inline-flex items-center gap-3   z-10 rounded-full   transition button button--bubble"
        :class="[
            { 'px-8 py-3 text-black': !isSquare },
            { 'w-12 h-12 justify-center items-center flex': isSquare }
        ]"
        :style="!isSquare
            ? { height: size + 'px', '--hover-color': color }
            : { width: size + 'px', height: size + 'px' }"
        ref="button"
        @mouseenter="playAnimation"
      >
       <slot />
      </a>

      <span class="button--bubble__effect-container">
        <span :style="{ backgroundColor: color }" class="circle top-left"></span>
        <span :style="{ backgroundColor: color }" class="circle top-left"></span>
        <span :style="{ backgroundColor: color }" class="circle top-left"></span>

        <span class="button effect-button rounded-full " :style="{ backgroundColor: color }"></span>

        <span :style="{ backgroundColor: color }" class="circle bottom-right"></span>
        <span :style="{ backgroundColor: color }" class="circle bottom-right"></span>
        <span :style="{ backgroundColor: color }" class="circle bottom-right"></span>
      </span>
    </span>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { gsap } from "gsap"

defineProps({
    color: {
        type: String,
        default: '#fff',
    },
    isSquare: {
        type: Boolean,
        default: false,
    },
    isFixed: {
        type: Boolean,
        default: false,
    },
    href: {
        type: [String, Object],
        default: '#',
    },
    size: {
        type: [String],
        default: '50',
    },
});

const button = ref(null)
let timeline

const playAnimation = () => {
  timeline.restart()
}

onMounted(() => {
  const container = button.value.parentElement

  const circlesTopLeft = container.querySelectorAll(".circle.top-left")
  const circlesBottomRight = container.querySelectorAll(".circle.bottom-right")
  const effectButton = container.querySelector(".effect-button")

  const tl = gsap.timeline()
  const tl2 = gsap.timeline()

  tl.to(circlesTopLeft, {
    duration: 1.2,
    x: -25,
    y: -25,
    ease: "slow(0.1, 0.7, false)"
  })

  tl.to(circlesTopLeft[0], { duration: 0.1, scale: 0.1, x: "+=6", y: "-=2" })
  tl.to(circlesTopLeft[1], { duration: 0.1, scaleX: 1, scaleY: 0.8, x: "-=10", y: "-=7" }, "-=0.1")
  tl.to(circlesTopLeft[2], { duration: 0.1, scale: 0.2, x: "-=15", y: "+=6" }, "-=0.1")

  tl.to(circlesTopLeft, {
    duration: 1,
    scale: 0,
    opacity: 0
  })

  tl2.to(circlesBottomRight, {
    duration: 1.1,
    x: 30,
    y: 30,
    ease: "slow(0.1, 0.7, false)"
  })

  tl2.to(circlesBottomRight[0], { duration: 0.1, scale: 0.1, x: "-=6", y: "+=3" })
  tl2.to(circlesBottomRight[1], { duration: 0.1, scale: 0.8, x: "+=7", y: "+=3" }, "-=0.1")
  tl2.to(circlesBottomRight[2], { duration: 0.1, scale: 0.2, x: "+=15", y: "-=6" }, "-=0.2")

  tl2.to(circlesBottomRight, {
    duration: 1,
    scale: 0,
    opacity: 0
  })

  timeline = gsap.timeline({ paused: true })

  timeline.add(tl)
  timeline.to(effectButton, { duration: 0.8, scaleY: 1.1 }, 0.1)
  timeline.add(tl2, 0.2)
  timeline.to(effectButton, {
    duration: 1.8,
    scale: 1,
    ease: "elastic.out(1.2,0.4)"
  }, 1.2)

  timeline.timeScale(2.6)
})
</script>

<style scoped lang="scss">
.bubble-wrapper {
  animation: hue-rotate 10s linear infinite;
}

.button {
  position: relative;
}

.button--bubble__container {
  position: relative;
  display: inline-block;
}

.button--bubble__effect-container {
  position: absolute;
  width: 200%;
  height: 400%;
  top: -150%;
  left: -50%;
  pointer-events: none;
}

.circle {
  position: absolute;
  width: 25px;
  height: 25px;
  border-radius: 50%;
}

.circle.top-left {
  top: 40%;
  left: 27%;
}

.circle.bottom-right {
  bottom: 40%;
  right: 27%;
}

.effect-button {
  position: absolute;
  width: 50%;
  height: 25%;
  top: 50%;
  left: 25%;
  transform: translateY(-50%);
}

</style>