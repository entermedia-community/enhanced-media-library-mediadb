window.wp = window.wp || {};

( function( $, _ ) {
    
    var media = wp.media,
        l10n = media.view.l10n,
        original = {};
 
   
    
    
    _.extend( media.controller.Library.prototype, {    
        
        uploading: function( attachment ) {            
    
            var dateFilter, Filters, taxFilter,
                content = this.frame.content,
                selection = this.get('selection'),
                library = this.get('library');
    
    
            if ( 'upload' === content.mode() ) {
                this.frame.content.mode('browse');
            }
            
            if ( wp.Uploader.queue.length == 1 ) {
            
                dateFilter = content.get().toolbar.get( 'dateFilter' );
                Filters = content.get().toolbar.get( 'filters' );
                
                if ( ! _.isUndefined(dateFilter) && 'all' !== dateFilter.$el.val() ) {
                    dateFilter.$el.val( 'all' ).change();
                }
                
                if ( ! _.isUndefined(Filters) && 'all' !== Filters.$el.val() ) {
                    Filters.$el.val( 'all' ).change();
                }
                
                $.each( wpuxss_eml_taxonomies, function( taxonomy, values ) {
                            
                    taxFilter = content.get().toolbar.get( taxonomy+'-filter' );
                    
                    if ( ! _.isUndefined(taxFilter) && 'all' !== taxFilter.$el.val() ) {
                        taxFilter.$el.val( 'all' ).change();
                    }
                });
            }
            
            if ( wp_version < '4.0' || this.get( 'autoSelect' ) ) {                
                
                if ( wp.Uploader.queue.length == 1 && selection.length ) {
                    selection.reset(); 
                }
                selection.add( attachment );
                selection.trigger( 'selection:unsingle' );
                selection.trigger( 'selection:single' );
            }    
        }
    });
    
    
    
    
    var newEvents = { 'click input'  : 'preSave' };
    _.extend( newEvents, media.view.AttachmentCompat.prototype.events );
    
    _.extend( media.view.AttachmentCompat.prototype, { 
        
        events: newEvents,
        
        preSave: function() {
            
            this.noRender = true;
            
            media.model.Query.cleanQueries();
        },
        
        render: function() {
    
            var compat = this.model.get('compat');
    
            if ( ! compat || ! compat.item ) {
                return;
            }
            
            if ( this.noRender ) {
                return this;
            }
    
            this.views.detach();
            this.$el.html( compat.item );
            this.views.render();
            return this;
        }
    });
    
    
    
    
    _.extend( media.view.AttachmentFilters.prototype, {
        
        change: function() {
            
            var filter = this.filters[ this.el.value ],
                selection = this.controller.state().get( 'selection' );
                
            if ( filter && selection.length && ! wp.Uploader.queue.length ) {
                selection.reset();
            }
            
            if ( filter ) {
                this.model.set( filter.props );
            }
        }
    });
    
    
    
    
    media.view.AttachmentFilters.Taxonomy = media.view.AttachmentFilters.extend({
        
        id: function() {
            
            return 'media-attachment-'+this.options.taxonomy+'-filters';
        },
        
        className: function() {
            
            // TODO: get rid of excess class name that duplicates id
            return 'attachment-filters eml-attachment-filters attachment-'+this.options.taxonomy+'-filter';
        },
        
        createFilters: function() {
            
            var filters = {},
                self = this;
    
            _.each( self.options.termList || {}, function( term, key ) {
                
                var term_id = term['term_id'],
                    term_name = $("<div/>").html(term['term_name']).text();
                    
                filters[ term_id ] = {
                    text: term_name,
                    priority: key+2
                };
                
                filters[term_id]['props'] = {};
                filters[term_id]['props'][self.options.taxonomy] = term_id;
            });
            
            filters.all = {
                text: self.options.termListTitle,
                priority: 1
            };
            
            filters['all']['props'] = {};
            filters['all']['props'][self.options.taxonomy] = null;
    
            this.filters = filters;
        }
    });
    
    
    
    original.AttachmentsBrowser = {
        
        initialize: media.view.AttachmentsBrowser.prototype.initialize,
        createToolbar: media.view.AttachmentsBrowser.prototype.createToolbar
    };
        
    _.extend( media.view.AttachmentsBrowser.prototype, {
        
        initialize: function() {
            
            original.AttachmentsBrowser.initialize.apply( this, arguments );
            
            this.on( 'ready', this.fixLayout, this );
            
            $( window ).on( 'resize', _.debounce( _.bind( this.fixLayout, this ), 15 ) );
            
            // ACF compatibility
            $( document ).on( 'click', '.acf-expand-details', _.debounce( _.bind( this.fixLayout, this ), 250 ) );
        },
        
        fixLayout: function() {
                
            var $browser = this.$el,
                $attachments = $browser.find('.attachments'),
                $uploader = $browser.find('.uploader-inline'),
                $toolbar = $browser.find('.media-toolbar');
            
            
            if ( wp_version < '4.0' ) {
                
                if ( 'absolute' == $attachments.css( 'position' ) && 
                    $browser.height() > $toolbar.height() + 20 ) { 
                        
                    $attachments.css( 'top', $toolbar.height() + 20 + 'px' );  
                    $uploader.css( 'top', $toolbar.height() + 20 + 'px' );
                }
                else if ( 'absolute' == $attachments.css( 'position' ) ) {
                    $attachments.css( 'top', '50px' );
                    $uploader.css( 'top', '50px' );
                }
                else if ( 'relative' == $attachments.css( 'position' ) ) {
                    $attachments.css( 'top', '0' );
                    $uploader.css( 'top', '0' );
                }
                
                // TODO: find a better place for it, something like fixLayoutOnce
                $toolbar.find('.media-toolbar-secondary').prepend( $toolbar.find('.instructions') );
                
                return;
            }
            
            
            if ( ! this.controller.isModeActive( 'select' ) && 
                 ! this.controller.isModeActive( 'eml-grid' ) ) {
                return;
            }
    
            if ( this.controller.isModeActive( 'select' ) ) {
                
                $attachments.css( 'top', $toolbar.height() + 10 + 'px' );  
                $uploader.css( 'top', $toolbar.height() + 10 + 'px' );
                $browser.find('.eml-loader').css( 'top', $toolbar.height() + 10 + 'px' );
                
                // TODO: find a better place for it, something like fixLayoutOnce
                $toolbar.find('.media-toolbar-secondary').prepend( $toolbar.find('.instructions') );
            }
    
            if ( this.controller.isModeActive( 'eml-grid' ) ) {
                
                $browser.css( 'top', $toolbar.outerHeight() + 20 + 'px' );
                $toolbar.css( 'top', - $toolbar.outerHeight() - 30 + 'px' );
            }
        },
    
        createToolbar: function() {
            
            var filters = this.options.filters,
                self = this,
                i = 1;
            
            original.AttachmentsBrowser.createToolbar.apply( this, arguments );
            
            if ( -1 !== $.inArray( filters, [ 'uploaded', 'all', 'eml' ] ) ) {
    
                if ( wp_version >= '4.0' && ! this.controller.isModeActive( 'grid' ) ) {
                
                    this.toolbar.set( 'dateFilterLabel', new media.view.Label({
                        value: l10n.filterByDate,
                        attributes: {
                            'for': 'media-attachment-date-filters'
                        },
                        priority: -75
                    }).render() );
                    this.toolbar.set( 'dateFilter', new media.view.DateFilter({
                        controller: this.controller,
                        model:      this.collection.props,
                        priority: -75
                    }).render() );
                }
            }
            
            $.each( wpuxss_eml_taxonomies, function( taxonomy, values ) {
                
                if ( values.term_list && -1 !== $.inArray( filters, [ 'uploaded', 'all', 'eml' ] ) ) {
                    
                    self.toolbar.set( taxonomy+'-filter', new media.view.AttachmentFilters.Taxonomy({
                        controller: self.controller,
                        model: self.collection.props,
                        priority: -80 + 10*i++,
                        taxonomy: taxonomy, 
                        termList: values.term_list,
                        termListTitle: values.list_title,
                    }).render() );
                }
            });
        }
    });
    
    
    
    
    // a copy from media-grid.js | for WP less than 4.1
    if ( _.isUndefined( media.view.DateFilter ) ) {
        media.view.DateFilter = media.view.AttachmentFilters.extend({
            
            id: 'media-attachment-date-filters',
        
            createFilters: function() {
                var filters = {};
                _.each( media.view.settings.months || {}, function( value, index ) {
                    filters[ index ] = {
                        text: value.text,
                        props: {
                            year: value.year,
                            monthnum: value.month
                        }
                    };
                });
                filters.all = {
                    text:  l10n.allDates,
                    props: {
                        monthnum: false,
                        year:  false
                    },
                    priority: 10
                };
                this.filters = filters;
            }
        });
    }
    
    
    
    
    // TODO: move to the PHP side
    $('body').addClass('eml-media-css');
    
    
})( jQuery, _ );