export class SiteComponent {
  constructor() {
    const fileUploads = document.querySelectorAll("input[data-show-images-in]");
    if (fileUploads) {
      this.initPhotoUpload(fileUploads);
    }
  }

  private initPhotoUpload(fileUploads) {
    Array.from(fileUploads).forEach((fu: HTMLInputElement) => {
      fu.addEventListener("change", () => {
        const output = document.querySelector(
          `#${fu.getAttribute("data-show-images-in")}`
        ) as HTMLElement;
        if (output) {
          output.innerHTML = "";
          const files = fu.files;
          Array.from(files).forEach((file) => {
            if (/\.(jpe?g|png|gif)$/i.test(file.name)) {
              const reader = new FileReader();
              reader.addEventListener(
                "load",
                function () {
                  var image = new Image();
                  image.height = 100;
                  image.title = file.name;
                  image.src = this.result as string;
                  output.appendChild(image);
                },
                false
              );
              reader.readAsDataURL(file);
            }
          });
        }
      });
    });
  }
}
