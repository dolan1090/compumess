import "./component";
import "./preview";

Shopware.Service("cmsService").registerCmsBlock({
  name: "image-text-5-colums",
  category: "text-image",
  label: "Image Text 5 Colums",
  component: "sw-cms-block-image-text-5-colums",
  previewComponent: "sw-cms-preview-image-text-5-colums",
  defaultConfig: {
    marginBottom: "20px",
    marginTop: "20px",
    marginLeft: "20px",
    marginRight: "20px",
    sizingMode: "boxed",
  },
  slots: {
    item1_Top: "image",
    item1_Bottom: "text",
    item2_Top: "image",
    item2_Bottom: "text",
    item3_Top: "image",
    item3_Bottom: "text",
    item4_Top: "image",
    item4_Bottom: "text",
    item5_Top: "image",
    item5_Bottom: "text",
  },
});
