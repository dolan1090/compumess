import "./component";
import "./preview";

Shopware.Service("cmsService").registerCmsBlock({
  name: "image-text-2-colums",
  category: "text-image",
  label: "Image Text 2 Colums",
  component: "sw-cms-block-image-text-2-colums",
  previewComponent: "sw-cms-preview-image-text-2-colums",
  defaultConfig: {
    marginBottom: "20px",
    marginTop: "20px",
    marginLeft: "20px",
    marginRight: "20px",
    sizingMode: "boxed",
  },
  slots: {
    leftTop: "image",
    leftBottom: "text",
    rightTop: "image",
    rightBottom: "text",
  },
});
