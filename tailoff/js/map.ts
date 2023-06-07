// @ts-nocheck
import Vue from "vue";
import RepairMap, { i18n } from "@statikbe/repair-map";
import RepcitMap from "./vue/RepcitMap.vue";
import RepairMapFilters from "./vue/RepairMapFilters.vue";

Vue.use(RepairMap);

const app = new Vue({
  i18n,
  el: "#repcitMap",
  components: {
    RepcitMap,
    RepairMapFilters
  },
  data: () => ({
    activeFilter: null
  }),
  methods: {
    toggleFilter(type) {
      if (this.activeFilter == type) {
        this.activeFilter = null;
      } else {
        this.activeFilter = type;
      }
    }
  }
});
