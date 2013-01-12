/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

/**
 * This file includes the cache and access for feed items
 */

var News = News || {};

(function(){

    /*##########################################################################
     * Items
     *########################################################################*/
    /**
     * Creates a new item instance and tells it to put the items into
     * the selected div
     * @param cssSelector the selector of the div which holds the ul for the feeds
     */
    var Items = function(cssSelector){
        var self = this;
        this._$articleList = $(cssSelector);
        this._$articleList.scrollTop(0);
        this._itemCache = new ItemCache();
        this._loadRequest = null;

        this._setScrollBottom();
        $(window).resize(function(){
            self._setScrollBottom();
        });

        // mark items whose title was hid under the top edge as read
        this._scrollTimeoutMiliSecs = 100;
        this._markReadTimeoutMiliSecs = 500;
        this._isScrolling = false;
        this._$articleList.scroll(function(){
            // prevent too many scroll requests;
            if(!self._isScrolling){
                self._isScrolling = true;
                setTimeout(function(){
                    self._isScrolling = false;
                }, self._scrollTimeoutMiliSecs);

                $(this).children('ul').children('.feed_item:not(.read)').each(function(){
                    var item = this;
                    var itemOffset = $(item).position().top;
                    if(itemOffset <= 0){
                        setTimeout(function(){
                            self._markItemAsReadTimeout(item);
                        }, self._markReadTimeoutMiliSecs);
                    }
                });
                //self._markCurrentlyViewed();
            }
        });

        this._itemCache.populate(this._$articleList.children('ul'));
    };

    /**
     * Marks an item as read which is called by the timeout
     * @param item the dom item
     */
    Items.prototype._markItemAsReadTimeout = function(item) {
        var itemId = parseInt($(item).data('id'), 10);
        var itemOffset = $(item).position().top;
        var cachedItem = this._itemCache.getItem(itemId);
        if(itemOffset < 0){
            if(!cachedItem.isLocked()){
                // lock item to prevent massive request when scrolling
                cachedItem.setLocked(true);
                cachedItem.setRead(true);
            }
        }
    };

    /**
     * Loads the feeds into the righ view
     * @param type the type (MenuNodeType)
     * @param id the id
     * @param onSuccessCallback a callback that is executed when the loading succeeded
     */
    Items.prototype.load = function(type, id, onSuccessCallback) {
        if(this._loadRequest !== null){
            this._loadRequest.abort();
        }

        this._lastActiveFeedId = id;
        this._lastActiveFeedType = type;

        var self = this;
        var data = {
            id: id,
            type: type,
            mostRecentItemId: this._itemCache.getMostRecentItemId(type, id)
        };

        this._$articleList.addClass('loading');
        this._$articleList.children('ul').hide();

        this._loadRequest = $.post(OC.filePath('news', 'ajax', 'loadfeed.php'), data, function(jsonData) {
            // prevent loading in selected feeds that are not active any more when
            // the post finishes later
            if(self._lastActiveFeedType === type && self._lastActiveFeedId === id){
                if(jsonData.status == 'success'){
                    self._$articleList.empty(); // FIXME: does this also removed cached items?
                    self._itemCache.populate(jsonData.data.feedItems);

                    var $items = self._itemCache.getFeedHtml(type, id);
                    self._$articleList.append($items);
                    self._$articleList.scrollTop(0);
                    onSuccessCallback();
                } else {
                    OC.dialogs.alert(t('news', 'Error while loading the feed'), t('news', 'Error'));
                    self._$articleList.children('ul').show();
                }
                self._$articleList.removeClass('loading');
                self._setScrollBottom();
            }
        });
    };


    /**
     * Jumps to the next visible element
     */
    Items.prototype.jumpToNext = function() {
        var self = this;
        var notJumped = true;
        $('.feed_item').each(function(){
            if(notJumped && $(this).position().top > 1){
                var id = parseInt($(this).data('id'), 10);
                self._jumpToElemenId(id);
                notJumped = false;
            }
        });
    };

    /**
     * Jumps to the previous visible element
     */
    Items.prototype.jumpToPrevious = function() {
        var self = this;
        var notJumped = true;
        $('.feed_item').each(function(){
            if(notJumped && $(this).position().top >= 0){
                var previous = $(this).prev();
                if(previous.length > 0){
                    var id = parseInt(previous.data('id'), 10);
                    self._jumpToElemenId(id);
                }
                notJumped = false;
            }
        });
        // in case we scroll more than the last element, just jump back to the
        // last one
        if(notJumped){
            var $items = $('.feed_item');
            if($items.length > 0){
                var id = parseInt($items.last().data('id'), 10);
                self._jumpToElemenId(id);
            }
        }
    };

    /**
     * Empties the item cache
     */
    Items.prototype.emptyItemCache = function() {
        this._itemCache.empty();
    };

    /**
     * Marks all items of a feed as read
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Items.prototype.markAllRead = function(type, id) {
        this._itemCache.markAllRead(type, id);
    };

    /**
     * Returns the most recent id of a feed from the cache
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the most recent id that is loaded on the page or 0
     */
    Items.prototype.getMostRecentItemId = function(type, id) {
        return this._itemCache.getMostRecentItemId(type, id);
    };

    /**
     * Jumps to an element in the article list
     * @param number the number of the item starting with 0
     */
    Items.prototype._jumpToElemenId = function(id) {
        $elem = $('.feed_item[data-id=' + id + ']');
        this._$articleList.scrollTop(
            $elem.offset().top - this._$articleList.offset().top + this._$articleList.scrollTop());
        //this._markCurrentlyViewed();
    };

    /**
     * Adds padding to the bottom to be able to scroll the last element beyond
     * the top area
     */
    Items.prototype._setScrollBottom = function() {
        var padding = this._$articleList.height() - 80;
        this._$articleList.children('ul').css('padding-bottom', padding + 'px');
    };


    /**
     * Returns a jquery node by searching for its id
     * @param id the id of the node
     * @return the jquery node
     */
    Items.prototype._findNodeById = function(id) {
        id = parseInt(id, 10);
        return this._$articleList.find('.feed_item[data-id="' + id + '"]');
    };

    /**
     * Marks the currently viewed element as viewed
     */
    Items.prototype._markCurrentlyViewed = function() {
        var self = this;
        $('.viewed').removeClass('viewed');
        var notFound = true;
        $('.feed_item').each(function(){
            var visiblePx = Math.ceil($(this).position().top + $(this).outerHeight());
            if(notFound && visiblePx > 90){
                $(this).addClass('viewed');
                notFound = false;
            }
        });
    };

    News.Items = Items;


    /*##########################################################################
     * ItemCache
     *########################################################################*/
    /**
     * A cache which holds the items of all loaded feeds
     */
    var ItemCache = function() {
        this._items = {};
        this._feeds = {};
    };

    /**
     * Returns an item from the cache
     */
    ItemCache.prototype.getItem = function(itemId) {
        itemId = parseInt(itemId, 10);
        return this._items[itemId];
    };


    /**
     * Marks all items of a feed as read
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    ItemCache.prototype.markAllRead = function(type, id) {
        var itemId;
        var ids = this._getItemIdTimestampPairs(type, id);
        for(var i=0; i<ids.length; i++){
            itemId = ids[i].key;
            this._items[itemId].setReadLocally();
        }
    };

    /**
     * Adds Html elements to the cache
     * @param html the html for a complete list with items
     */
    ItemCache.prototype.populate = function(html) {
        var self = this;
        $html = $(html);
        $html.children('.feed_item').each(function(){
            var item = new Item(this);
            self._items[item.getId()] = item;
            self._feeds[item.getFeedId()] = self._feeds[item.getFeedId()] || {};
            self._feeds[item.getFeedId()][item.getId()] = item;
        });
    };

    /**
     * Empties the cache
     */
    ItemCache.prototype.empty = function() {
        this._items = {};
        this._feeds = {};
    };


    ItemCache.prototype._getItemIdTimestampPairs = function(type, id) {
        var pairs = [];
        if(Object.keys(this._feeds).length === 0 || Object.keys(this._items).length === 0){
            return pairs;
        }

        switch(type){

            case News.MenuNodeType.Feed:
                if(this._feeds[id] === undefined){
                    return pairs;
                }
                $.each(this._feeds[id], function(key, value){
                    pairs.push({key: value.getId(), value: value.getTimeStamp()});
                });
                break;

            case News.MenuNodeType.Folder:
                // this is a bit of a hack and not that beautiful^^
                var feedIds = News.Objects.Menu.getFeedIdsOfFolder(id);
                for(var i=0; i<feedIds.length; i++){
                    var feedPairs = this._getItemIdTimestampPairs(News.MenuNodeType.Feed, feedIds[i]);
                    for(var j=0; j<feedPairs.length; j++){
                        pairs.push(feedPairs[j]);
                    }
                }
                break;

            case News.MenuNodeType.Subscriptions:
                $.each(this._items, function(key, value){
                    pairs.push({key: value.getId(), value: value.getTimeStamp()});
                });
                break;

            case News.MenuNodeType.Starred:
                $.each(this._items, function(key, value){
                    if(value.isImportant()){
                        pairs.push({key: value.getId(), value: value.getTimeStamp()});
                    }
                });
                break;
		
        }
        return pairs;
    };

    /**
     * Returns all the ids of feeds for a type sorted by timestamp ascending
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return all the ids of feeds for a type sorted by timestamp ascending
     */
    ItemCache.prototype._getSortedItemIds = function(type, id) {
        var pairs = this._getItemIdTimestampPairs(type, id);

        var sorted = pairs.slice(0).sort(function(a, b) {
           return a.value - b.value;
        });

        var itemIds = [];
        for (var i = 0, len = sorted.length; i < len; ++i) {
            itemIds[i] = sorted[i].key;
        }
        return itemIds;
    };

    /**
     * Returns the most recent id of a feed
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the most recent id that is loaded on the page or 0
     */
    ItemCache.prototype.getMostRecentItemId = function(type, id) {
        var itemIds = this._getSortedItemIds(type, id);
        if(itemIds.length === 0){
            return 0;
        } else {
            return itemIds[itemIds.length-1];
        }
    };

    /**
     * Returns the html for a specific feed
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the jquery html element for a complete feed
     */
    ItemCache.prototype.getFeedHtml = function(type, id) {
        var itemIds = this._getSortedItemIds(type, id);
        itemIds.reverse(); // reverse for showing newest item first
        var $html = $('<ul>');
        for(var i=0; i<itemIds.length; i++){
            var item = this._items[itemIds[i]];
            if(i === 0){
                //item.setViewed(true);
            }
            // show items
            if(News.Objects.Menu.isShowAll() ||
               type === News.MenuNodeType.Starred ||
               !item.isRead()){
                var $itemHtml = item.getHtml();
                $itemHtml.removeClass('keep_unread');
                $html.append($itemHtml);
            }
            // hide the additional feed anme if its just a normal feed
            if(type === News.MenuNodeType.Feed){
                item.showAdditionalFeedTitle(false);
            } else {
                item.showAdditionalFeedTitle(true);
            }
        }
        return $html;
    };

    /*##########################################################################
     * Item
     *########################################################################*/
    /**
     * An item which binds the appropriate html and event handlers
     * @param html the html to populate the item
     */
     var Item = function(html){
        this._starred = false;
        this._$html = $(html);
        this._id = parseInt(this._$html.data('id'), 10);
        this._feedId = parseInt(this._$html.data('feedid'), 10);
        this._read = this._$html.hasClass('read');
        this._locked = false;
        this._important = this._$html.find('li.star').hasClass('important');
        // get timestamp for sorting
        var $stamp = this._$html.find('.timestamp');
        this._timestamp = parseInt($stamp.html(), 10);
        $stamp.remove();
        // open all links in new tabs
        this._$html.find('.body a').attr('target', '_blank');
        this._bindItemEventListeners();
     };

    /**
     * @return the id of the item
     */
    Item.prototype.getId = function() {
        return this._id;
    };


    /**
     * @return the feeid of the item
     */
    Item.prototype.getFeedId = function() {
        return this._feedId;
    };

    /**
     * @return the jquery html of the item
     */
    Item.prototype.getHtml = function() {
        return this._$html;
    };

    /**
     * @return a unix timestamp when the articles was published
     */
    Item.prototype.getTimeStamp = function() {
        return this._timestamp;
    };

    /**
     * @return true if starred, otherwise false
     */
    Item.prototype.isImportant = function() {
        return this._important;
    };

    /**
     * @return true if starred, otherwise false
     */
    Item.prototype.isRead = function() {
        return this._read;
    };

    /**
     * @param locked true will lock, false unlock
     */
    Item.prototype.setLocked = function(locked) {
        this._locked = locked;
    };

    /**
     * @param viewed true will add the viewed class, false remove
     */
    Item.prototype.setViewed = function(viewed) {
        if(viewed){
            this._$html.addClass('viewed');
        } else {
            this._$html.removeClass('viewed');
        }
    };


    /**
     * @param show if true, show the feedtitle on the left side of the author
     */
    Item.prototype.showAdditionalFeedTitle = function(show) {
        if(show){
            this._$html.find('.from_feed').show();
        } else {
            this._$html.find('.from_feed').hide();
        }
    };

    /**
     * @return true if locked, otherwise false
     */
    Item.prototype.isLocked = function() {
        return this._locked;
    };

    /**
     * Adds only the read class, used for marking all read
     */
    Item.prototype.addReadClass = function() {
        this._$html.addClass('read');
    };

    /**
     * Marks the item as read only locally without telling the server
     */
    Item.prototype.setReadLocally = function() {
        this._read = true;
        this._$html.addClass('read');
    };

    /**
     * Marks the item read
     * @param read true marks it read, false unread
     */
    Item.prototype.setRead = function(read) {
        var status;
        var self = this;

        if(read === this._read){
            this.setLocked(false);
            return;
        }

        if(read && this._isKeptUnread()){
            this.setLocked(false);
            return;
        }

        if(read){
            status = 'read';
            self._$html.addClass('read');
        } else {
            status = 'unread';
            self._$html.removeClass('read');
        }

        var data = {
            itemId: this._id,
            status: status
        };

        $.post(OC.filePath('news', 'ajax', 'setitemstatus.php'), data, function(jsonData){
            if(jsonData.status == 'success'){
                if(read){
                    self._$html.addClass('read');
                    self._read = true;
                    News.Objects.Menu.decrementUnreadCount(News.MenuNodeType.Feed, self._feedId);
                } else {
                    self._$html.removeClass('read');
                    self._read = false;
                    News.Objects.Menu.incrementUnreadCount(News.MenuNodeType.Feed, self._feedId);
                }
            } else {
                if(read){
                    self._$html.removeClass('read');
                } else {
                    self._$html.addClass('read');
                }
                OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
            }
            self.setLocked(false);
        });
    };

    /**
     * Binds listeners on the feed item
     */
    Item.prototype._bindItemEventListeners = function() {
        var self = this;

        // single hover on item should mark it as read too
        this._$html.find('.item_title a').click(function(){
            var $item = $(this).parent().parent('.feed_item');
            var itemId = $item.data('id');
            self.setRead(true);
        });

        // bind click to load feed when the from_feed link is viewable
        this._$html.find('.from_feed').click(function(){
            News.Objects.Menu.load(News.MenuNodeType.Feed, self.getFeedId());
            return false;
        });

        // single hover on item should mark it as read too
        this._$html.find('.body').click(function(){
            var $item = $(this).parent('.feed_item');
            var itemId = $item.data('id');
            self.setRead(true);
        });

        // mark or unmark as important
        this._$html.find('li.star').click(function(){
            var $item = $(this).parent().parent().parent('.feed_item');
            var itemId = $item.data('id');
            self._toggleImportant(itemId);
        });

        // toggle logic for the keep unread handler
        this._$html.find('.keep_unread').click(function(){
            var $item = $(this).parent().parent().parent('.feed_item');
            var itemId = $item.data('id');
            self._toggleKeepUnread();
        });

        //this._$html.find('time.timeago').timeago();
    };

    /**
     * Toggles the keep unread state
     */
    Item.prototype._toggleKeepUnread = function() {
        var checkBox = this._$html.find('.keep_unread input[type=checkbox]');
        if(this._isKeptUnread()){
            this._$html.removeClass('keep_unread');
            checkBox.prop("checked", false);
        } else {
            this.setRead(false);
            checkBox.prop("checked", true);
            this._$html.addClass('keep_unread');
        }
    };

    /**
     * @return true if kept unread
     */
    Item.prototype._isKeptUnread = function() {
        return this._$html.hasClass('keep_unread');
    };


    /**
     * Toggles the important state
     */
    Item.prototype._toggleImportant = function() {
        var status;
        var important = !this._important; // remember, we toggle important
        var self = this;
        var $star = this._$html.find('li.star');

        if(important){
            status = 'important';
            $star.addClass('important');
        } else {
            status = 'unimportant';
            $star.removeClass('important');
        }

        var data = {
            itemId: this._id,
            status: status
        };

        $.post(OC.filePath('news', 'ajax', 'setitemstatus.php'), data, function(jsondata){
            if(jsondata.status == 'success'){
               if(important){
                    $star.addClass('important');
                    News.Objects.Menu.incrementUnreadCount(News.MenuNodeType.Starred, self._feedId);
                } else {
                    $star.removeClass('important');
                    News.Objects.Menu.decrementUnreadCount(News.MenuNodeType.Starred, self._feedId);
                }
                self._important = !self._important;
            } else{
                // rollback on error
                if(important){
                    $star.addClass('important');
                } else {
                    $star.removeClass('important');
                }
                OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
            }
        });
    };

})();
