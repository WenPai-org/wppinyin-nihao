(function() {
    var __ = window.wp.i18n.__;
    var { insert, applyFormat,toggleFormat } = window.wp.richText;
    var registerFormatType = window.wp.richText.registerFormatType;
    var createElement = window.wp.element.createElement;
    var RichTextToolbarButton = window.wp.blockEditor.RichTextToolbarButton;

    var unregisterFormatType = window.wp.richText.unregisterFormatType;
    //unregisterFormatType( 'wppy-nihao/button' );

    registerFormatType( 'wppy-nihao/rt', {
        title: __( 'Ruby Character', 'wppy-nihao' ),
        tagName: 'rt',
        className: null,
        edit:function( {isActive, value, onChange} ) {
            return createElement('rt',null);
        }
    
    } );

    registerFormatType( 'wppy-nihao/ruby', {
        title: __( '注音', 'wppy-nihao' ),
        tagName: 'ruby',
        className: null,
        edit: function ( props ) {
            var value = props.value;
            var isActive = props.isActive;
            var onChange = props.onChange;
            return createElement(RichTextToolbarButton, {
                title: __( '注音', 'wppy-nihao' ),
                tagName:'ruby',
                className:null,
                onClick:function(){
                    let ruby = '';
                    if ( ! isActive ) {
                        ruby = window.prompt( __( '注音', 'wppy-nihao' ) ) || value.text.substr( value.start, value.end -value.start );
                        const rubyEnd   = value.end;
                        const rubyStart = value.start;
                        value = insert( value, ruby, rubyEnd );
                        value.start = rubyStart;
                        value.end   = rubyEnd + ruby.length;
                        value = applyFormat( value, {
                            type: 'wppy-nihao/ruby'
                        }, rubyStart, rubyEnd + ruby.length );
                        value = applyFormat( value, {
                            type: 'wppy-nihao/rt'
                        }, rubyEnd, rubyEnd + ruby.length );
                    } else {
                        alert(value.text);
                        value = toggleFormat( value, {
                            type: 'wppy-nihao/ruby'
                        } );
                    }
                    return onChange( value );
                },
                icon: createElement('img', { src: assets.url+'/images/wppy.svg', style: { width:'24px',margin: '2px 6px 0 2px' } }),
                isActive: isActive,
                //shortcutType: 'primary',
                //shortcutCharacter: '.',
                className: 'toolbar-button-with-text wppy_nihao',
            })
        }
    } );
})();

