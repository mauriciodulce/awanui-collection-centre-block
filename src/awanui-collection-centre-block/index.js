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

/**
 * Collection Centre Block component for WordPress Gutenberg editor.
 *
 * Displays a collection centre selector and preview in the editor,
 * allowing users to select and display information about collection centres.
 *
 * @param {Object} props - Component properties
 * @param {Object} props.attributes - Block attributes containing centreId
 * @param {Function} props.setAttributes - Function to update block attributes
 * @returns {JSX.Element} The Collection Centre Block component
 */
function CollectionCentreBlock({ attributes, setAttributes }) {
  const { centreId } = attributes;
  const [centres, setCentres] = useState([]);
  const [selectedCentre, setSelectedCentre] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetchingCentre, setIsFetchingCentre] = useState(false);
  const [error, setError] = useState(null);

  const blockProps = useBlockProps({ className: "awanui-collection-centre-block" });

  useEffect(() => {
    fetchCentres();
  }, []);

  // Efecto para obtener datos del centro cuando cambia centreId
  useEffect(() => {
    if (centreId && centres.length > 0) {
      fetchSelectedCentre(centreId);
    } else {
      setSelectedCentre(null);
    }
  }, [centreId, centres]);

  /**
   * Fetches the list of available collection centres from the API.
   *
   * Updates the centres state with the fetched data and handles loading
   * and error states appropriately.
   *
   * @async
   * @returns {Promise<void>}
   */
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

  /**
   * Fetches detailed information for a specific collection centre.
   *
   * First attempts to find the centre in the already loaded centres array,
   * then makes an API call if not found. Updates the selectedCentre state.
   *
   * @async
   * @param {string|number} id - The ID of the centre to fetch
   * @returns {Promise<void>}
   */
  const fetchSelectedCentre = async (id) => {
    setIsFetchingCentre(true);
    setError(null);

    try {
      // Primero buscar en los centros ya cargados
      const foundCentre = centres.find(centre => centre.id == id);

      if (foundCentre) {
        setSelectedCentre(foundCentre);
        setIsFetchingCentre(false);
        return;
      }

      // Si no se encuentra, hacer una petición específica
      const response = await apiFetch({
        path: `/awanui/v1/centres/${id}`
      });

      if (response) {
        setSelectedCentre(response);
      } else {
        throw new Error("Centre not found");
      }
    } catch (err) {
      setError(err.message || "Failed to load centre details");
      setSelectedCentre(null);
    } finally {
      setIsFetchingCentre(false);
    }
  };

  const centreOptions = [
    { label: __("Select a collection centre...", "awanui-collection-centre-block"), value: "" },
    ...centres.map((centre) => ({
      label: centre.name || centre.title || `Centre ${centre.id}`,
      value: centre.id,
    })),
  ];

  /**
   * Handles the change event when a user selects a different collection centre.
   *
   * Updates the block attributes with the newly selected centre ID.
   *
   * @param {string|number} newCentreId - The ID of the newly selected centre
   * @returns {void}
   */
  const handleCentreChange = (newCentreId) => {
    setAttributes({ centreId: newCentreId });
  };

  /**
   * Renders the preview of the selected collection centre in the editor.
   *
   * Displays different states: placeholder when no centre is selected,
   * loading spinner while fetching data, error message if centre not found,
   * or the complete centre information including address, phone, and hours.
   *
   * @returns {JSX.Element} The rendered centre preview component
   */
  const renderCentrePreview = () => {
    if (!centreId) {
      return (
        <div className="awanui-centre-placeholder">
          <p>{__("Please select a collection centre from the sidebar.", "awanui-collection-centre-block")}</p>
        </div>
      );
    }

    if (isFetchingCentre) {
      return (
        <div className="awanui-centre-loading">
          <Spinner />
          <p>{__("Loading centre details...", "awanui-collection-centre-block")}</p>
        </div>
      );
    }

    if (!selectedCentre) {
      return (
        <div className="awanui-centre-error">
          <p>{__("Centre not found or failed to load.", "awanui-collection-centre-block")}</p>
        </div>
      );
    }

    const {
      name,
      title,
      address,
      city,
      region,
      post_code,
      phone_number,
      opening_hours = [],
      location
    } = selectedCentre;

    const centreName = name || title || 'Collection Centre';
    const centreAddress = address || (location?.address) || 'Address not available';
    const centrePhone = phone_number || 'Phone not available';
    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(centreAddress)}`;

    return (
      <div className="awanui-centre-preview">
        <div className="awanui-centre-info">
          <h3 className="awanui-centre-name">{centreName}</h3>

          <div className="awanui-centre-details">
            <div className="awanui-centre-address">
              <strong>{__("Address:", "awanui-collection-centre-block")}</strong>
              <p>
                {centreAddress}
                {city && <><br />{city}</>}
                {region && <><br />{region}</>}
                {post_code && <><br />{post_code}</>}
              </p>
            </div>

            <div className="awanui-centre-phone">
              <strong>{__("Phone:", "awanui-collection-centre-block")}</strong>
              <p>{centrePhone}</p>
            </div>

            {opening_hours.length > 0 && (
              <div className="awanui-centre-hours">
                <strong>{__("Opening Hours:", "awanui-collection-centre-block")}</strong>
                <div>
                  {opening_hours.map((hour, index) => (
                    <div key={index}>{hour}</div>
                  ))}
                </div>
              </div>
            )}

            <div className="awanui-centre-directions">
              <a
                href={mapsUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="awanui-directions-link"
              >
                {__("Get Directions", "awanui-collection-centre-block")}
              </a>
            </div>
          </div>
        </div>

        <div className="awanui-editor-notice">
          <em>{__("Preview in editor - this will render on the frontend", "awanui-collection-centre-block")}</em>
        </div>
      </div>
    );
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
        {renderCentrePreview()}
      </div>
    </>
  );
}

registerBlockType("awanui/awanui-collection-centre-block", {
  edit: CollectionCentreBlock,
  save: () => null,
});
