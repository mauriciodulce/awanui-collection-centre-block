import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  SelectControl,
  Spinner,
  Notice,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

import "./style.scss";

function CollectionCentreBlock({ attributes, setAttributes }) {
  const { centreId } = attributes;
  const [centres, setCentres] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  const blockProps = useBlockProps({ className: "awanui-collection-centre-block" });

  useEffect(() => {
    fetchCentres();
  }, []);

  const fetchCentres = async () => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await apiFetch({ path: "/awanui/v1/centres" });

      if (response && Array.isArray(response)) {
        setCentres(response);
      } else {
        throw new Error("Invalid API response");
      }
    } catch (err) {
      setError(err.message || "Failed to load collection centres");
    } finally {
      setIsLoading(false);
    }
  };

  const centreOptions = [
    { label: __("Select a collection centre...", "awanui-collection-centre-block"), value: "" },
    ...centres.map((centre) => ({
      label: centre.name || centre.title || `Centre ${centre.id}`,
      value: centre.id,
    })),
  ];

  const handleCentreChange = (newCentreId) => {
    setAttributes({ centreId: newCentreId });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Collection Centre Settings", "awanui-collection-centre-block")}>
          {isLoading && <Spinner />}

          {error && (
            <Notice status="error" isDismissible={false}>
              {error}
            </Notice>
          )}

          <SelectControl
            label={__("Select Collection Centre", "awanui-collection-centre-block")}
            value={centreId}
            options={centreOptions}
            onChange={handleCentreChange}
            disabled={isLoading}
          />

          {centreId && (
            <Notice status="info" isDismissible={false}>
              {__("Selected Centre ID:", "awanui-collection-centre-block")} {centreId}
            </Notice>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <p>
          {centreId
            ? __("Collection centre selected. It will be rendered on the frontend.", "awanui-collection-centre-block")
            : __("Please select a collection centre.", "awanui-collection-centre-block")}
        </p>
      </div>
    </>
  );
}

registerBlockType("awanui/awanui-collection-centre-block", {
  edit: CollectionCentreBlock,
  save: () => null,
});
