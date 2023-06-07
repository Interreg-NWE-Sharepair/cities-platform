import { ArrayPrototypes } from "../utils/prototypes/array.prototypes";
// import Glide from "@glidejs/glide";
import Glide, {
  Controls,
  Breakpoints,
  Swipe
} from "@glidejs/glide/dist/glide.modular.esm";
import { DOMHelper } from "../utils/domHelper";

ArrayPrototypes.activateFrom();

export class GlideComponent {
  constructor() {
    const sliders = Array.from(document.querySelectorAll(".js-slider"));
    this.processSliders(sliders);

    DOMHelper.onDynamicContent(
      document.documentElement,
      ".js-slider",
      sliders => {
        this.processSliders(Array.from(sliders));
      }
    );
  }

  private processSliders(sliders: Array<Element>) {
    sliders.forEach(slider => {
      slider.classList.remove("js-slider");
      const sliderID = slider.getAttribute("id");
      const glide = new (Glide as any)("#" + sliderID, {
        type: "carousel",
        perView: 6,
        breakpoints: {
          1200: {
            perView: 6
          },
          980: {
            perView: 5
          },
          820: {
            perView: 4
          },
          480: {
            perView: 2
          }
        }
      });
      // glide.mount({});
      glide.mount({ Controls, Breakpoints, Swipe });
    });
  }
}
