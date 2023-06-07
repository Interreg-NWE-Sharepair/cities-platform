const defaultTheme = require("tailwindcss/defaultTheme");

const colorShade = require("./tailoff/tailwind/color-shades");
// const underlineAnimation = require("./tailoff/tailwind/underline-animation");
const aspectRatio = require("tailwindcss-aspect-ratio");

const siteColors = {
  primary: {
    DEFAULT: "#71B8C5",
    contrast: "#ffffff",
    hover: "#5A939D",
    hoverContrast: "#ffffff",
  },
  secondary: {
    DEFAULT: "#9C7A97",
    contrast: "#ffffff",
    hover: "#7C6178",
    hoverContrast: "#ffffff",
  },
};

module.exports = {
  theme: {
    borderWidth: {
      DEFAULT: "1px",
      0: "0",
      1: "1px",
      2: "2px",
      3: "3px",
    },
    container: {
      center: true,
      padding: defaultTheme.spacing["4"],
    },
    fontFamily: {
      base: "'Titillium Web', sans-serif",
    },
    screens: {
      xs: "480px",
      sm: "660px",
      md: "820px",
      lg: "980px",
      xl: "1200px",
    },
    colors: {
      ...defaultTheme.colors,
      ...siteColors,
      black: "#333333",
      light: "#F9F9F9",
      divider: "rgba(51,51,51,0.2)",
      "black-40": "rgba(51,51,51,0.4)",
    },
    aspectRatio: {
      none: 0,
      square: [1, 1],
      "16/9": [16, 9],
      "4/3": [4, 3],
      "21/9": [21, 9],
    },
    extend: {
      fontSize: {
        "2xs": [".6rem", ".75rem"],
        lg: ["1.125rem", "1.5625rem"], // 18px - 25px
        xl: ["1.25rem", "1.5625rem"], // 20px - 25px
        "2xl": ["1.375rem", "1.5625rem"], // 22px - 25px
        "3xl": ["1.75rem", "2.1875rem"], // 28px - 35px
        "4xl": ["2.1875rem", "2.5rem"], // 35px - 40px
        "5xl": ["2.5rem", "3.125rem"], // 40px - 50px
      },
      maxWidth: {
        flyout: "280px",
        modal: "700px",
        logo: "150px",
      },
      maxHeight: {
        logo: "85px",
        "logo-mobile": "50px",
      },
      zIndex: {
        99: "99",
        100: "100",
      },
      boxShadow: {
        card: "0 0 30px 0 rgba(0,0,0,0.15)",
        focus: "0 0 0 3px rgba(238,71,55,0.5)",
      },
      borderRadius: {
        card: "30px",
        panel: "40px",
      },
      inset: {
        "1/2": "50%",
      },
    },
  },
  variants: {
    textDecoration: ["responsive", "hover", "focus", "group-hover"],
    textColor: ["responsive", "hover", "focus", "group-hover"],
  },
  plugins: [
    colorShade(siteColors),
    // underlineAnimation,
    aspectRatio,
  ],
};
