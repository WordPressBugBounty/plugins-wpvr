/**
 * WPVR Gutenberg Block
 * 
 * A modern implementation of the WPVR tour block using ES6 and WordPress Block API
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { useEffect, useState } = wp.element;
const { 
    InspectorControls,
    useBlockProps
} = wp.blockEditor;
const { 
    TextControl, 
    SelectControl, 
    ColorPalette, 
    PanelBody,
    RangeControl,
    __experimentalUnitControl: UnitControl,
    __experimentalNumberControl: NumberControl
} = wp.components;
const { apiFetch } = wp;

// Block icon as SVG
const blockIcon = <svg width="20" height="20" viewBox="0 0 20 20">
    <path d="M16.1,16.6h-2.5c-1,0-1.9-0.6-2.4-1.5L11,14.5c-0.2-0.4-0.5-0.6-0.9-0.6c-0.4,0-0.8,0.2-0.9,0.6l-0.3,0.6 c-0.4,0.9-1.3,1.5-2.4,1.5H3.9c-2.2,0-3.9-1.8-3.9-3.9V7.3c0-2.2,1.8-3.9,3.9-3.9h12.2c2.2,0,3.9,1.8,3.9,3.9v1.5 c0,0.4-0.3,0.8-0.8,0.8c-0.4,0-0.8-0.3-0.8-0.8V7.3c0-1.3-1.1-2.3-2.3-2.3H3.9C2.6,4.9,1.6,6,1.6,7.3v5.4c0,1.3,1.1,2.3,2.3,2.3 h2.6c0.4,0,0.8-0.2,0.9-0.6l0.3-0.6c0.4-0.9,1.3-1.5,2.4-1.5c1,0,1.9,0.6,2.4,1.5l0.3,0.6c0.2,0.4,0.5,0.6,0.9,0.6h2.5 c1.3,0,2.3-1.1,2.3-2.3c0-0.4,0.3-0.8,0.8-0.8c0.4,0,0.8,0.3,0.8,0.8C20,14.9,18.2,16.6,16.1,16.6L16.1,16.6z M16.7,9.4 c0-1.3-1.1-2.3-2.3-2.3C13,7.1,12,8.1,12,9.4s1.1,2.3,2.3,2.3C15.6,11.7,16.7,10.7,16.7,9.4L16.7,9.4z M15.1,9.4 c0,0.4-0.4,0.8-0.8,0.8c-0.4,0-0.8-0.4-0.8-0.8s0.4-0.8,0.8-0.8C14.8,8.6,15.1,9,15.1,9.4L15.1,9.4z M8,9.4C8,8.1,7,7.1,5.7,7.1 S3.3,8.1,3.3,9.4s1.1,2.3,2.3,2.3S8,10.7,8,9.4L8,9.4z M6.4,9.4c0,0.4-0.4,0.8-0.8,0.8c-0.4,0-0.8-0.4-0.8-0.8s0.4-0.8,0.8-0.8 C6.1,8.6,6.4,9,6.4,9.4L6.4,9.4z M6.4,9.4" />
</svg>;

// Available colors for the border
const BORDER_COLORS = [
    { name: 'red', color: '#f00' },
    { name: 'white', color: '#fff' },
    { name: 'blue', color: '#00f' },
    { name: 'black', color: '#000' },
    { name: 'gray', color: '#888' },
];

// Border style options
const BORDER_STYLES = [
    { value: 'none', label: __('None', 'wpvr') },
    { value: 'solid', label: __('Solid', 'wpvr') },
    { value: 'dotted', label: __('Dotted', 'wpvr') },
    { value: 'dashed', label: __('Dashed', 'wpvr') },
    { value: 'double', label: __('Double', 'wpvr') },
];

// Width unit options
const WIDTH_UNITS = [
    { value: 'px', label: 'px' },
    { value: '%', label: '%' },
    { value: 'vw', label: 'vw' },
    { value: 'fullwidth', label: __('Fullwidth', 'wpvr') },
];

// Height unit options
const HEIGHT_UNITS = [
    { value: 'px', label: 'px' },
    { value: 'vh', label: 'vh' },
];

// Radius unit options
const RADIUS_UNITS = [
    { value: 'px', label: 'px' },
    { value: '%', label: '%' },
];

/**
 * WPVR Block Edit component using functional component and hooks
 */
function WpvrEdit({ attributes, setAttributes }) {
    const [tourOptions, setTourOptions] = useState([{ value: "0", label: __("None", "wpvr") }]);
    
    // Fetch tour data when component mounts
    useEffect(() => {
        apiFetch({ path: 'wpvr/v1/panodata' })
            .then(data => {
                if (data && Array.isArray(data)) {
                    setTourOptions(data);
                }
            })
            .catch(error => {
                console.error('Error fetching WPVR tour data:', error);
            });
    }, []);

    // Handle width unit change
    const handleWidthUnitChange = (value) => {

        if (value === 'fullwidth' && attributes.width === 'fullwidth') {
            setAttributes({ 
                width: 'fullwidth',
                width_unit: ''
            });
        } else {

            if( value === 'fullwidth' ){
                setAttributes({ 
                    width: 'fullwidth',
                    width_unit: ''
                });
            }else {
                setAttributes({ 
                    width: attributes.width_unit === '' ? '600' : attributes.width,
                    width_unit: value
                });
            }

            
        }

        console.log(attributes.width, value);
    };

    // Get block props with custom styles
    const blockProps = useBlockProps({
        className: 'wpvr-block-preview',
        style: {
            width: attributes.width_unit ? `${attributes.width}${attributes.width_unit}` : (attributes.width === 'fullwidth' ? '100%' : undefined),
            height: `${attributes.height}${attributes.height_unit}`,
            borderRadius: attributes.radius ? `${attributes.radius}${attributes.radius_unit}` : undefined,
            borderStyle: attributes.border_style,
            borderColor: attributes.border_color,
            borderWidth: attributes.border_width ? `${attributes.border_width}px` : undefined,
        }
    });


    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Tour Settings', 'wpvr')} initialOpen={true}>
                    <SelectControl
                        label={__('Select Tour', 'wpvr')}
                        value={attributes.id}
                        options={tourOptions}
                        onChange={(value) => setAttributes({ id: value })}
                    />
                    
                    <div className="wpvr-dimension-control">
                        <p>{__('Tour Width', 'wpvr')}</p>
                        <div className="wpvr-dimension-inputs">
                            <NumberControl
                                value={attributes.width}
                                onChange={(value) => setAttributes({ width: value })}
                                disabled={attributes.width === 'fullwidth'}
                            />
                            <SelectControl
                                value={attributes.width === 'fullwidth' ? 'fullwidth' : attributes.width_unit}
                                options={WIDTH_UNITS}
                                onChange={handleWidthUnitChange}
                            />
                        </div>
                    </div>
                    
                    <div className="wpvr-dimension-control">
                        <p>{__('Tour Height', 'wpvr')}</p>
                        <div className="wpvr-dimension-inputs">
                            <NumberControl
                                value={attributes.height}
                                onChange={(value) => setAttributes({ height: value })}
                            />
                            <SelectControl
                                value={attributes.height_unit}
                                options={HEIGHT_UNITS}
                                onChange={(value) => setAttributes({ height_unit: value })}
                            />
                        </div>
                    </div>
                    
                    <div className="wpvr-dimension-control">
                        <p>{__('Tour Mobile Height', 'wpvr')}</p>
                        <div className="wpvr-dimension-inputs">
                            <NumberControl
                                value={attributes.mobile_height}
                                onChange={(value) => setAttributes({ mobile_height: value })}
                            />
                            <SelectControl
                                value={attributes.mobile_height_unit}
                                options={HEIGHT_UNITS}
                                onChange={(value) => setAttributes({ mobile_height_unit: value })}
                            />
                        </div>
                    </div>
                </PanelBody>
                
                <PanelBody title={__('Appearance', 'wpvr')} initialOpen={false}>
                    <div className="wpvr-dimension-control">
                        <p>{__('Tour Border Radius', 'wpvr')}</p>
                        <div className="wpvr-dimension-inputs">
                            <NumberControl
                                value={attributes.radius}
                                onChange={(value) => setAttributes({ radius: value })}
                            />
                            <SelectControl
                                value={attributes.radius_unit}
                                options={RADIUS_UNITS}
                                onChange={(value) => setAttributes({ radius_unit: value })}
                            />
                        </div>
                    </div>
                    
                    <RangeControl
                        label={__('Tour Border Width', 'wpvr')}
                        value={parseInt(attributes.border_width) || 0}
                        onChange={(value) => setAttributes({ border_width: value.toString() })}
                        min={0}
                        max={20}
                    />
                    
                    <SelectControl
                        label={__('Tour Border Style', 'wpvr')}
                        value={attributes.border_style}
                        options={BORDER_STYLES}
                        onChange={(value) => setAttributes({ border_style: value })}
                    />
                    
                    <div className="wpvr-color-control">
                        <p>{__('Tour Border Color', 'wpvr')}</p>
                        <ColorPalette
                            colors={BORDER_COLORS}
                            value={attributes.border_color}
                            onChange={(value) => setAttributes({ border_color: value })}
                            disableCustomColors={false}
                        />
                    </div>
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps}>
                <div className="wpvr-block-content">
                    {attributes.id !== "0" ? (
                        <div className="wpvr-tour-preview">
                            <div className="wpvr-tour-placeholder">
                                {__('WPVR Tour Preview', 'wpvr')}
                                <p className="wpvr-tour-details">
                                    ID: {attributes.id}<br/>
                                    {__('Width', 'wpvr')}: {attributes.width}{attributes.width_unit}<br/>
                                    {__('Height', 'wpvr')}: {attributes.height}{attributes.height_unit}<br/>
                                    {__('Mobile Height', 'wpvr')}: {attributes.mobile_height}{attributes.mobile_height_unit}<br/>
                                    {attributes.radius && `${__('Radius', 'wpvr')}: ${attributes.radius}${attributes.radius_unit}`}
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="wpvr-no-tour-selected">
                            {__('Please select a tour from the block settings panel.', 'wpvr')}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

// Register the block
registerBlockType('wpvr/wpvr-block', {
    title: __('WPVR Tour', 'wpvr'),
    description: __('Add a virtual reality tour to your content.', 'wpvr'),
    icon: blockIcon,
    category: 'media',
    keywords: [__('vr', 'wpvr'), __('tour', 'wpvr'), __('panorama', 'wpvr'), __('virtual reality', 'wpvr')],
    
    attributes: {
        id: {
            type: 'string',
            default: '0',
        },
        width: {
            type: 'string',
            default: '600',
        },
        width_unit: {
            type: 'string',
            default: 'px',
        },
        height: {
            type: 'string',
            default: '400',
        },
        height_unit: {
            type: 'string',
            default: 'px',
        },
        mobile_height: {
            type: 'string',
            default: '300',
        },
        mobile_height_unit: {
            type: 'string',
            default: 'px',
        },
        radius: {
            type: 'string',
            default: '0',
        },
        radius_unit: {
            type: 'string',
            default: 'px',
        },
        border_width: {
            type: 'string',
            default: '0',
        },
        border_style: {
            type: 'string',
            default: 'none',
        },
        border_color: {
            type: 'string',
            default: '',
        },
    },
    
    edit: WpvrEdit,
    
    save: () => {
        // Return null to use PHP render callback
        return null;
    },
});