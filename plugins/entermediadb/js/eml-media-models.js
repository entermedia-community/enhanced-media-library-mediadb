window.wp = window.wp || {};

( function( $, _ ) {
    
    var media = wp.media,
        Attachments = media.model.Attachments,
        Query = media.model.Query;
    
   
    
       
    _.extend( Query.prototype, {
        
        initialize: function( models, options ) {
            
            var allowed;

            options = options || {};
            Attachments.prototype.initialize.apply( this, arguments );

            this.args     = options.args;
            this._hasMore = true;
            this.created  = new Date();

            this.filters.order = function( attachment ) {
                var orderby = this.props.get('orderby'),
                    order = this.props.get('order');

                if ( ! this.comparator ) {
                    return true;
                }

                // We want any items that can be placed before the last
                // item in the set. If we add any items after the last
                // item, then we can't guarantee the set is complete.
                if ( this.length ) {
                    return 1 !== this.comparator( attachment, this.last(), { ties: true });

                // Handle the case where there are no items yet and
                // we're sorting for recent items. In that case, we want
                // changes that occurred after we created the query.
                } else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
                    return attachment.get( orderby ) >= this.created;

                // If we're sorting by menu order and we have no items,
                // accept any items that have the default menu order (0).
                } else if ( 'ASC' === order && 'menuOrder' === orderby ) {
                    return attachment.get( orderby ) === 0;
                }

                // Otherwise, we don't want any items yet.
                return false;
            };

            // Observe the central `wp.Uploader.queue` collection to watch for
            // new matches for the query.
            //
            // Only observe when a limited number of query args are set. There
            // are no filters for other properties, so observing will result in
            // false positives in those queries.
            allowed = [ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent', 'year', 'monthnum' ];
            if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() ) {
                this.observe( wp.Uploader.queue );
            }
        }
    });
    
   
    
    
    _.extend( Query, {
    
        queries: [],
        
        cleanQueries: function(){
            
            this.queries = [];
        },
        
        get: (function(){

            return function( props, options ) {
                
                var args       = {},
                    orderby    = Query.orderby,
                    defaults   = Query.defaultProps,
                    query,
                    cache      = !! props.cache || _.isUndefined( props.cache );

                delete props.query;
                delete props.cache;

                // Fill default args.
                _.defaults( props, defaults );

                // Normalize the order.
                props.order = props.order.toUpperCase();
                if ( 'DESC' !== props.order && 'ASC' !== props.order ) {
                    props.order = defaults.order.toUpperCase();
                }

                // Ensure we have a valid orderby value.
                if ( ! _.contains( orderby.allowed, props.orderby ) ) {
                    props.orderby = defaults.orderby;
                }

                // Generate the query `args` object.
                // Correct any differing property names.
                _.each( props, function( value, prop ) {
                    if ( _.isNull( value ) ) {
                        return;
                    }

                    args[ Query.propmap[ prop ] || prop ] = value;
                });

                // Fill any other default query args.
                _.defaults( args, Query.defaultArgs );

                // `props.orderby` does not always map directly to `args.orderby`.
                // Substitute exceptions specified in orderby.keymap.
                args.orderby = orderby.valuemap[ props.orderby ] || props.orderby;
                
                // Search the query cache for matches.
                if ( cache ) {
                    query = _.find( this.queries, function( query ) {
                        return _.isEqual( query.args, args );
                    });
                } else {
                    this.queries = [];
                }

                // Otherwise, create a new query and add it to the cache.
                if ( ! query ) {
                    query = new Query( [], _.extend( options || {}, {
                        props: props,
                        args:  args
                    } ) );
                    this.queries.push( query );
                }

                return query;
            };
        }())
    });
    
        
        
})( jQuery, _ );