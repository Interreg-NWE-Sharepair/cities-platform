<template>
  <div class="mt-4">
    <button
      class="mt-4 mr-4 btn btn--secondary"
      @click="$emit('change', 'CATEGORY')"
    >
      {{ categoryText }}
      <svg
        v-if="activeFilter == 'CATEGORY'"
        class="ml-2 icon"
        aria-hidden="true"
      >
        <use xlink:href="/icon/sprite.svg#chevron-up"></use>
      </svg>
      <svg v-else class="ml-2 icon" aria-hidden="true">
        <use xlink:href="/icon/sprite.svg#chevron-down"></use>
      </svg>
    </button>
    <button
      class="mt-4 mr-4 btn btn--secondary"
      @click="$emit('change', 'TYPE')"
    >
      {{ typeText }}
      <svg v-if="activeFilter == 'TYPE'" class="ml-2 icon" aria-hidden="true">
        <use xlink:href="/icon/sprite.svg#chevron-up"></use>
      </svg>
      <svg v-else class="ml-2 icon" aria-hidden="true">
        <use xlink:href="/icon/sprite.svg#chevron-down"></use>
      </svg>
    </button>
    <button
      class="mt-4 mr-4 btn btn--secondary"
      @click="$emit('change', 'LOCATION')"
      v-if="showLocation"
    >
      {{ locationText }}
      <svg
        v-if="activeFilter == 'LOCATION'"
        class="ml-2 icon"
        aria-hidden="true"
      >
        <use xlink:href="/icon/sprite.svg#chevron-up"></use>
      </svg>
      <svg v-else class="ml-2 icon" aria-hidden="true">
        <use xlink:href="/icon/sprite.svg#chevron-down"></use>
      </svg>
    </button>
  </div>
</template>

<script>
export default {
  props: {
    activeFilter: {
      type: String,
      default: null
    },
    typeText: {
      type: String,
      default: "Type"
    },
    categoryText: {
      type: String,
      default: "Category"
    },
    locationText: {
      type: String,
      default: "Location"
    },
    showLocation: {
      type: Boolean,
      default: false
    }
  }
};
</script>
