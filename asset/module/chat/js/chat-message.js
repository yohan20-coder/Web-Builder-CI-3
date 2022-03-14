function CicoolChat() {
  this.CHAT_INDICATOR_SENT_ICO = '<svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="15"><path fill="#92A58C" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>';
  this.CHAT_INDICATOR_READ_ICO = '<svg id="Layer_1"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="15"><path fill="#4FC3F7" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>'; 
  this.CHAT_INDICATOR_PENDING_ICO = '<svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15" width="16" height="15"><path fill="#92A58C" d="M10.91 3.316l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>';
  this.messages = [];
  this.lastChatClass = '';
  this.chatOffset = 50;
  this.intReadMessage = null;
}

CicoolChat.prototype.init = function() {
    this.showDateTop();
}

CicoolChat.prototype.showDateTop = function() {
    var wrapper = $('.chat-history-wrapper');
    if (wrapper.closest('.chat-date-current')) {
        /*wrapper.prepend(`<div class="chat-date-current">
             yesterday
      </div>`)*/
    }
}
CicoolChat.prototype.addOffset = function(offset) {
    this.chatOffset = parseInt(this.chatOffset) + 50;
}
CicoolChat.prototype.parseSingleMessage = function(message) {
    var messagePosition = message.message_user_id == userId ? 'user-right' : 'user-left';
    var needIcon = message.message_user_id == userId ? true : false;
    var icon = needIcon ? this.getIconStatus(message.status) : '';
    var classGroup = '';

    if (this.lastChatClass == messagePosition) {
        classGroup = 'margin-top:-12px';
    }
    this.lastChatClass = messagePosition;
    var idMessage = message.id;
    var message_txt = message.message;

    //var message = message.replace(/\[image=([^/]+)]/ig, '<a class="fancybox" href="'+BASE_URL+'/uploads/chat/$1"> <img class="chat-image-attchment" src="'+BASE_URL+'/uploads/chat/$1"></a>');
    reg = new RegExp(/\[image\=([0-9.\-A-Za-z]+)\]/ig);   
    var matches = message_txt.match(reg);
    $.each(matches, function(index, val) {
     var path = val.replace('[image=', '').replace(']', '');
     message_txt = message_txt.replace(val, '<a class="fancybox chat-image-funcy" href="'+BASE_URL+'/uploads/chat/'+path+'"> <img class="chat-image-attchment" src="'+BASE_URL+'/uploads/chat/'+path+'"></a>');
    });
    return ` <div data-message-id="`+idMessage+`" class="chat-item chat-item-user `+messagePosition+`" data-date="` + moment(message.created_at).format('YYYY-MM-DD') + `">
            <div class="chat-message chat-message-user" style="`+classGroup+`"" >
              ` + message_txt + `                    
              <div class="receiver-stat-icon">
              `+icon+`
              </div>
              <span class="chat-time">`+ moment(message.created_at).format('HH:MM')+`</span>
            </div><!-- 
            <div class="chat-date-time">
            </div>  -->
          </div>
  `;
}
CicoolChat.prototype.parseMessages = function(messages) {
    var chats = '';
    var obj = this;
    this.messages = messages;
    $.each(messages, function(index, val) {
        chats += obj.parseSingleMessage(val);
    });

    return chats;
}
CicoolChat.prototype.grouppingMessage = function() {
    var dates = [];

    function onlyUnique(value, index, self) { 
        return self.indexOf(value) === index;
    }

    $('body').find('.chat-item-user').each(function(index, el) {
        var date = $(this).attr('data-date'); 
        dates.push(date)
    });
    var unique = dates.filter( onlyUnique );

    $('.chat-date-separator').remove();
    $.each(unique, function(index, val) {
        var date =  moment(val);
        var now = moment()
        var label = date.format('DD/MM/YY');
        if (date.format('YYYY-MM-DD') == now.format('YYYY-MM-DD')) {
            label = 'Today';
        }
        if (now.diff(date, 'days') == 1 ) {
            label = 'Yesterday';
        }
        if (now.diff(date, 'days') >= 2 && now.diff(date, 'days') < 7) {
            label = date.format('dddd');
        }
        $(`<div class="chat-date-separator">
              `+label+`  
      </div>`).insertBefore('.chat-item.chat-item-user[data-date="'+val+'"]:first')
    });

    $('.chat-item-user').each(function(index, el) {
        if($(this).find('.chat-image-attchment').length > 1) {
            $(this).find('.chat-message-user').addClass('chat-item-user-with-images');
        } else  if($(this).find('.chat-image-attchment').length == 1) {
            $(this).find('.chat-message-user').addClass('chat-item-user-with-image');
        }
    });
    this.parseImage();
    return this;
}

CicoolChat.prototype.parseImage = function(status) {
   $('.chat-item-user').each(function(index, el) {
        if($(this).find('.chat-image-attchment').length > 1) {
            $(this).find('.chat-message-user').addClass('chat-item-user-with-images');
        } else  if($(this).find('.chat-image-attchment').length == 1) {
            $(this).find('.chat-message-user').addClass('chat-item-user-with-image');
        }
    });
    return this;
}


CicoolChat.prototype.getIconStatus = function(status) {
  if (status == 'pending') {
    return this.CHAT_INDICATOR_PENDING_ICO;
  } 
  else if (status == 'read') {
    return this.CHAT_INDICATOR_READ_ICO;
  } 

  return this.CHAT_INDICATOR_SENT_ICO;
}

CicoolChat.prototype.newPrivateChat = function (contactId, callback) {
    var obj = this;
    $.ajax({
        url: BASE_URL + 'administrator/chat/new_private_chat',
        type: 'GET',
        dataType: 'JSON',
        data: {
            contact_id: contactId
        },
    }).done(function(res) {
        if (res.success) {
            obj.resetBadgeNotif();
            callback(res.data);
            $('.chat-message-user-send').focus();
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}

CicoolChat.prototype.loadMessage = function (chatId, offset, callback) {
    var obj = this;
    $.ajax({
        url: BASE_URL + 'administrator/chat/get_message',
        type: 'GET',
        dataType: 'JSON',
        data: {
            chat_id: chatId,
            offset: offset,
        },
    }).done(function(res) {
        if (res.success) {
            obj.resetBadgeNotif();
            callback(res.data);
        }
    }).fail(function() {
    }).always(function() {
    });
}

CicoolChat.prototype.loadSearchMessage = function (chatId, messageId, callback) {
    var obj = this;
    $.ajax({
        url: BASE_URL + 'administrator/chat/get_search_message',
        type: 'GET',
        dataType: 'JSON',
        data: {
            chat_id: chatId,
            message_id: messageId,
        },
    }).done(function(res) {
        if (res.success) {
            obj.resetBadgeNotif();
            callback(res.data);
        }
    }).fail(function() {
    }).always(function() {
    });
}

CicoolChat.prototype.sendMessage = function (chatId, message, callback) {
    var data_post = []

    data_post.push({name: 'chat_id', value: chatId});
    data_post.push({name: 'message', value: message});
    data_post.push({name: csrf, value: token});

    $.ajax({
        url: BASE_URL + 'administrator/chat/send_message',
        type: 'POST',
        dataType: 'JSON',
        data: data_post,
    }).done(function(res) {
        if (res.success) {
            callback(res.data);
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}

CicoolChat.prototype.getMessage = function (chatId, callback) {
    $.ajax({
        url: BASE_URL + 'administrator/chat/get_message',
        type: 'GET',
        dataType: 'JSON',
        data: {
            chat_id: chatId
        },
    }).done(function(res) {
        if (res.success) {
            callback(res.data);
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });

    return this;
}



CicoolChat.prototype.parseSingleContact = function(contact) {
    var icon = '';
    if(typeof contact.last_message.status != 'undefined') {
      if (contact.last_message.message_user_id == userId) {
         icon = this.getIconStatus(contact.last_message.status )
      }
    }
    if(typeof contact.last_message.created_at != 'undefined') {
        var created_at = moment(contact.last_message.created_at).format('DD MMMM')

        if (moment(contact.last_message.created_at).format('YYYY-MMMM-DD') == moment().format('YYYY-MMMM-DD')) {
            created_at = moment(contact.last_message.created_at).format('HH:MM')
        }
    } else {
         var created_at = '-';
    }
    return `<div class="chat-item"
            data-id="`+contact.id+`"
            data-user-id="`+contact.user.id+`"
            data-user-username="`+contact.user.username+`"
            data-user-fullname="`+contact.user.full_name+`"
             >
             <div class="chat-contact-icon"><img src="`+BASE_URL+`/uploads/user/default.png" alt=""></div>
             <div class="chat-header">
              <div class="chat-date">`+created_at+`</div>
              <h4>`+contact.user.full_name+`</h4>
              </div>
             <div class="chat-body"><small class="chat-last-message">`+(typeof contact.last_message.message == 'undefined' ? '' :icon+' '+contact.last_message.message )+`</small>
             `+(contact.unread > 0 ? '<div class="pull-right counter-incomming-message">'+contact.unread+'</div>' : '')+`
             <img class="chat-typing" src="`+BASE_URL+`asset/module/chat/img/typing.svg" width="30px" alt="">
             </div>
           </div>
  `;
}
CicoolChat.prototype.parseContacts = function(contacts) {
    var html = '';
    var obj = this;
    this.contacts = contacts;
    $.each(contacts, function(index, val) {
        html += obj.parseSingleContact(val);
    });
    
    return html;
}


CicoolChat.prototype.parseSingleSearchItem = function(contact) {
    var icon = '';
    if(typeof contact.status != 'undefined') {
      if (contact.message_user_id == userId) {
         icon = this.getIconStatus(contact.status )
      }
    }
    if(typeof contact.created_at != 'undefined') {
        var created_at = moment(contact.created_at).format('DD MMMM')

        if (moment(contact.created_at).format('YYYY-MMMM-DD') == moment().format('YYYY-MMMM-DD')) {
            created_at = moment(contact.created_at).format('HH:MM')
        }
    } else {
         var created_at = '-';
    }
    return `<div class="chat-item"
            data-message-id="`+contact.id+`"
            data-user-id="`+contact.user.id+`"
            data-id="`+contact.chat_id+`"
            data-user-username="`+contact.user.username+`"
            data-user-fullname="`+contact.user.full_name+`"
             >
             
             <div class="chat-header">
              <div class="chat-date">`+created_at+`</div>
              <h4>`+contact.user.full_name+`</h4>
              </div>
             <div class="chat-body"><small>`+(typeof contact.message == 'undefined' ? '' :icon+' '+contact.message )+`</small></div>
           </div>
  `;
}
CicoolChat.prototype.parseSearchResults = function(contacts) {
    var html = '';
    var obj = this;
    this.contacts = contacts;
    $.each(contacts, function(index, val) {
        html += obj.parseSingleSearchItem(val);
    });
    
    return html;
}



CicoolChat.prototype.getConversations = function (callback) {
    $.ajax({
        url: BASE_URL + 'administrator/chat/get_chat',
        type: 'GET',
        dataType: 'JSON',
        data: {
        },
    }).done(function(res) {
        if (res.success) {
            callback(res.data.chats);
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}

CicoolChat.prototype.findChats = function (filter, callback) {
    $.ajax({
        url: BASE_URL + 'administrator/chat/find_chats',
        type: 'GET',
        dataType: 'JSON',
        data: {
            q : filter
        },
    }).done(function(res) {
        if (res.success) {
            callback(res.data.chats);
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}

CicoolChat.prototype.readMessage = function (chat_id, contact_id, callback) {
    $.ajax({
        url: BASE_URL + 'administrator/chat/read_message',
        type: 'GET',
        dataType: 'JSON',
        data: {
            chat_id : chat_id,
            contact_id : contact_id,
        },
    }).done(function(res) {
        if (res.success) {
            callback(res.data.chats);
        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}
CicoolChat.prototype.getConversationUser = function () {
   return {
    'username' : $('.chat-detail-header .chat-detail-username').val(), 
    'userId' : $('.chat-detail-header .chat-detail-id').val(), 
    'chatId' : $('.chat-detail-header .chat-id').val(), 
   }
}
CicoolChat.prototype.bottomButtonMessage = function () {
    var classes = this;
    if ($('.chat-contact-detail .chat-history').scrollTop() 
        < $('.chat-contact-detail .chat-history').prop('scrollHeight') - 500) 
     {
       $('.bottom-button-wrapper').fadeIn();
     } else {
      
     }

     if ($('.chat-contact-detail .chat-history').scrollTop() 
        > $('.chat-contact-detail .chat-history').prop('scrollHeight') - 500) 
     {
      if ($('.bottom-button-wrapper').is(':visible')) {
           this.resetBadgeNotif();
           $('.bottom-button-wrapper').fadeOut();
           var chatId = this.getConversationUser().chatId;
           var userId = this.getConversationUser().userId;
           
            clearInterval(this.intReadMessage);
            this.intReadMessage = setTimeout(function(){
              classes.readMessage(chatId, userId, function(){
                  $('.chat-item[data-id="'+chatId+'"]').find('.counter-incomming-message').fadeOut();
                })
            }, 1000);
       }
     }
     this.toggleBadge();
}
CicoolChat.prototype.toggleBadge = function () {
    if ($('.badge-count-will-read').html() == 0) {
        $('.badge-count-will-read').hide();
    } else {
        $('.badge-count-will-read').show();
    }
}
CicoolChat.prototype.addBadgeNotif = function () {
    var obj = $('.badge-count-will-read');
    if ($('.bottom-button-wrapper').is(':visible')) {
        obj.show();
        obj.html(parseInt(obj.html()) + 1);
    }
    return this;
}
CicoolChat.prototype.resetBadgeNotif = function () {
    var obj = $('.badge-count-will-read');

    obj.html('0');
    this.toggleBadge();
}