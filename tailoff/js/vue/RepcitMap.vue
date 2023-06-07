<template>
  <div>
    <repair-map
      :show-filter-buttons="false"
      :filter="activeFilter"
      @filter-close="closeFilter"
      :defaultCenter="defaultCenter"
      :defaultZoom="defaultZoom"
      :locale="locale"
      mapboxAccessToken="pk.eyJ1Ijoic3RhdGlrLWJ2YmEiLCJhIjoiY2xmOW9sY2ZoMmlzdTNvcjAzY2NpcXJ5cSJ9.LCrfm6Fkx-X1wgIR6bEVXA"
    >
      <template #locationTitle="{ location: { id, slug, name }, defaultClass }">
        <a :href="`${baseUrl}${id}`" :class="defaultClass">
          <span>{{ name[locale] || name.default }}</span>
        </a>
      </template>
    </repair-map>
  </div>
</template>

<script>
const RIcon = require("@statikbe/repair-components").RIcon;

export default {
  components: {
    RIcon
  },
  props: {
    activeFilter: {
      type: String,
      default: null
    },
    defaultCenter: {
      type: Array,
      default: null
    },
    defaultZoom: {
      type: Number,
      default: 14
    },
    locale: {
      type: String,
      default: null
    }
  },
  created: function() {
    this.baseUrl = this.$attrs["baseurl"];
  },
  methods: {
    closeFilter() {
      // this.activeFilter = null;
      this.$emit("close-filter", null);
    }
  }
};
</script>
