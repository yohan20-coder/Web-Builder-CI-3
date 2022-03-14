var chatRefreshInterval = 30000;
var intLoadChat = true;
var intReadMessage = null;
var curScroll = null;


function resetScrollAndLoadMessage() {
  intLoadChat = true;
  intReadMessage = null;
  curScroll = null;
}
function showFeatureBottom() {
    //$(' .btn-chat-sticker').hide();
    $('.tab-chat-feature-wrapper').show();
    $('.btn-chat-file').data('state', 2);
}

function hideFeatureBottom() {
    $('.chat-user-message-wrapper, .btn-chat-sticker').show();
    $('.tab-chat-feature-wrapper').hide();
    $('.btn-chat-file').data('state', 1);
}

function resizeChat() {
    var headHeight = $('.chat-detail-header').height();
    var chatUserHeight = $('.chat-contact-detail .chat-user').height();
    if (chatUserHeight < 300) {
        //$('.chat-history-wrapper').height(chatUserHeight+400)
    }
    var chatExpandHeight = $('.chat-history-wrapper').height();
    $('.chat-history').height(chatExpandHeight - (chatUserHeight + headHeight) + 10);
    $('.chat-user').width($('.chat-history').width() - 20)
    $('.chat-box').height($(window).height() - 48);
}
$(window).resize(function(event) {
    /*
      autosize($('.chat-message'));*/
});

$(document).ready(function() {
    var notify = new FireNotif();
    var audio = new Audio('notif.mp3');
    var firebaseUrl = 'https://fire-notif.firebaseio.com/';
    var firebaseKey = 'q1rcKg8zxPUySGntLfnIYNMFufq2sss';
    notify.setKey(firebaseKey).setUrl(firebaseUrl).setPath('notify-user');
    notify.subscribe(function(data) {
        if (data.chat_id == $('.chat-id').val()) {
            $('.chat-contact-detail .chat-history').append(ccChat.parseSingleMessage({
                id: data.id,
                message_user_id: data.message_user_id,
                status: data.status,
                created_at: moment(data.created_at).format('YYYY-MM-DD'),
                message: data.message,
            }))
            if (data.message_user_id != userId) {
                ccChat.addBadgeNotif();
            }
            if ($('.chat-contact-detail .chat-history').scrollTop() > $('.chat-contact-detail .chat-history').prop('scrollHeight') - 500) {
                if (data.message_user_id != userId) {
                    clearInterval(intReadMessage);
                    intReadMessage = setTimeout(function(){
                      ccChat.readMessage(data.chat_id, data.message_user_id, function() {
                          $('.chat-item[data-id="' + data.chat_id + '"]').find('.counter-incomming-message').fadeOut();
                      })
                    }, 1000);
                }
                $('.chat-contact-detail .chat-history').scrollTop(99999999);
            }
            $('.chat-item-typing').addClass('chat-item-typing-inactive')
        }
        $('.chat-item-user[data-message-id="no-id"]').remove();
        ccChat.parseImage();
        refreshConversation(function() {
            $('.chat-item[data-id="' + $('.chat-id').val() + '"]').addClass('active-chat-contact')
        });

    });

    $('.btn-new-chat').click(function(event) {
        event.preventDefault();
        $('.new-chat-contact').animate({
            left: 0
        }, 'fast');
    });

    $('.btn-back-chat').click(function(event) {
        event.preventDefault();
        $('.new-chat-contact').animate({
            left: '-30%'
        }, 'fast');
    });

    $("[contenteditable]").focusout(function() {
        var element = $(this);
        if (!element.text().trim().length) {
            element.empty();
        }
    });

    $(`<div class="chat-date-separator">
              Yesterday  
      </div>`).insertBefore($('[data-date="2019-03-25"]').first())
    $('.main-footer').remove();
    /*
          autosize($('.chat-message'));*/
    setInterval(resizeChat, 100);
    resizeChat();
    $(document).on('keyup keypress keydown change', '.chat-message', function(event) {
        resizeChat();
    });

    $('.web-body').addClass('sidebar-collapse');
    $('.chat-contact-wrapper').slimScroll({
        height: 450,
        size: '10px',
        alwaysVisible: true
    })
    $('.chat-history').css('overflow', 'auto');
    $('.chat-history').css('overflow', 'auto');
    $('body,html').css('overflow', 'hidden');
    /*slimScroll({
        height: 400,
        size: '10px',
        alwaysVisible : true,
    })*/
    var ccChat = new CicoolChat;
    ccChat.init();

    function refreshConversation(callback) {
        ccChat.getConversations(function(data) {
            var html = ccChat.parseContacts(data);
            $('.chat-contact-message-wrapper').html(html)
            if (typeof callback != 'undefined') {
                callback(data);
            }
        });

    }

    function findChats() {
        $('.chat-search-message-wrapper').html(`
        <div class="no-search-result">Searching For Chats..</div>
        `)
        ccChat.findChats($('.search-input').val(), function(data) {
            var html = ccChat.parseSearchResults(data);
            var title = `
        <div class="search-result-title">MESSAGES</div>`;
            $('.chat-search-message-wrapper').html(title + html)
            if (data.length <= 0) {
                $('.chat-search-message-wrapper').html(`
            <div class="no-search-result">No Search Results</div>
            `)
            }
        });

    }
    var intChat = null;
    $(document).on('click', '.chat-contact-wrapper:not(.chat-search-message-wrapper) .chat-item', function(event) {
        if ($(this).hasClass('active-chat-contact')) {
            return;
        }
        event.preventDefault();
        var userName = $(this).attr('data-user-username');
        var userFullName = $(this).attr('data-user-fullname');
        var userId = $(this).attr('data-user-id');
        var chatId = $(this).attr('data-id');
        $('.chat-detail-id').val(userId)
        $('.chat-contact-message-wrapper .chat-item').removeClass('active-chat-contact')
        $('.chat-contact-message-wrapper .chat-item[data-user-id=' + userId + ']').addClass('active-chat-contact')
        $('.chat-detail-username').val(userName)
        $('.chat-detail-fullname').val(userFullName)
        $('.chat-id').val(chatId)
        $('.chat-detail-name').html(userFullName)
        $('.new-chat-contact').animate({
            left: '-30%'
        }, 'fast');
        ccChat.newPrivateChat(userId, function(data) {
            $('.chat-contact-detail .chat-history').html('')
            $('.chat-contact-detail .chat-history').html(ccChat.parseMessages(data.messages)).scrollTop(999999999)
            $('.chat-id').val(data.chat.id)
            clearInterval(intChat);
            ccChat.grouppingMessage();
            $('.chat-item[data-id="' + data.chat.id + '"]').find('.counter-incomming-message').fadeOut();
            resetScrollAndLoadMessage();
        });

    });
     $(document).on('click', '.chat-search-message-wrapper .chat-item', function(event) {
        if ($(this).hasClass('active-chat-contact')) {
            return;
        }
        event.preventDefault();
        var userName = $(this).attr('data-user-username');
        var userFullName = $(this).attr('data-user-fullname');
        var userId = $(this).attr('data-user-id');
        var chatId = $(this).attr('data-id');
        var messageId = $(this).attr('data-message-id');
        $('.chat-detail-id').val(userId)
        $('.chat-contact-message-wrapper .chat-item').removeClass('active-chat-contact')
        $('.chat-contact-message-wrapper .chat-item[data-user-id=' + userId + ']').addClass('active-chat-contact')
        $('.chat-detail-username').val(userName)
        $('.chat-detail-fullname').val(userFullName)
        $('.chat-id').val(chatId)
        $('.chat-detail-name').html(userFullName)
        $('.new-chat-contact').animate({
            left: '-30%'
        }, 'fast');
        ccChat.loadSearchMessage(chatId,messageId, function(data) {
            $('.chat-contact-detail .chat-history').html('')
            $('.chat-contact-detail .chat-history').html(ccChat.parseMessages(data.messages)).scrollTop(999999999)
            clearInterval(intChat);
            ccChat.grouppingMessage();
            var itemFound = $('.chat-item-user[data-message-id="'+messageId+'"]');
            itemFound.addClass('chat-item-find-result')
            $('.chat-item[data-id="' + chatId + '"]').find('.counter-incomming-message').fadeOut();
              $('.chat-contact-detail .chat-history').animate({
              scrollTop: itemFound.offset().top
          }, 1000);

             
        });

    });

    function sendMessage() {
        var chatId = $('.chat-id').val();
        var message = $('.chat-message-user-send').html();
        var message_live_view = message;
        var images_ico = '';
        var images = $('input[name^=chat_image_name]').map(function(idx, elem) {
            var text = $(elem).attr('name');
            var matches = text.match(/chat_image_name\[(\d+)\]/);
            var picture = $('.qq-thumbnail-selector').eq(idx).attr('src');
            images_ico += ' <img class="chat-image-attchment-holder" src="' + picture + '"> '
            return ' [image=' + $('[name="chat_image_uuid[' + matches[1] + ']"]').val() + '/' + $(elem).val() + '] ';
        }).get();
        if (images != null) {
            message += images;
            message_live_view = message_live_view + ' ' + images_ico;
        }
        message.trim()

        if (message.length == 0) {
          return;
        }
        message_live_view.trim()
        message_live_view = message_live_view.replace(/^( |<br>)*(.*?)( |<br>)*$/, "$2");
        $('.chat-contact-detail .chat-history').append(ccChat.parseSingleMessage({
            id: "no-id",
            message_user_id: userId,
            status: 'pending',
            created_at: moment().format('YYYY-MM-DD'),
            message: message_live_view,
        })).scrollTop(99999999999)
        $('.chat-message-user-send ').html('')
        ccChat.sendMessage(chatId, message, function(data) {
            $('#chat_image_galery').find('li').each(function() {
                $('#chat_image_galery').fineUploader('deleteFile', $(this).attr('qq-file-id'));
            });
            ccChat.grouppingMessage();
            $('.chat-contact-detail .chat-history').scrollTop(99999999999)

        });
        hideFeatureBottom();


    }
    $('.btn-chat-send').click(function(event) {
        event.preventDefault();
        sendMessage();
    });

    $(document).on('click', '.chat-contact-message-wrapper .chat-item', function(event) {
        event.preventDefault();
        $('.chat-contact-message-wrapper .chat-item').removeClass('active-chat-contact');
        $(this).addClass('active-chat-contact');
    });

    refreshConversation(function() {
        $('.chat-contact-wrapper .chat-item:first').trigger('click')
    });

    $('.chat-message-user-send').bind('keydown', 'return', function(event) {
        event.preventDefault();
        sendMessage();
        return false;
    });

    var intTyping = null;
    var intRemoveTyping = null;
    var typingData = {};
    var typingActive = false;
    var typingActiveChatList = {};
    var notifyTyping = new FireNotif();
    notifyTyping.setKey(firebaseKey).setUrl(firebaseUrl)
    $('.chat-message-user-send').on('keypress', function(event) {
        var chatId = $('.chat-id').val();
        var receiverId = $('.chat-detail-id').val();
        notifyTyping.setPath('notify-writing/' + receiverId);
        clearInterval(intTyping);
        typingData = {
            from: userId,
            chat_id: chatId
        };
        typingActive = true;
    });

    setInterval(function() {
        if (typingActive) {
            typingActive = false;
            notifyTyping.pushNotify(typingData);
        }
    }, 1500);
    var notifyTypingReceive = new FireNotif();
    notifyTypingReceive.setKey(firebaseKey).setUrl(firebaseUrl)
    notifyTypingReceive.setPath('notify-writing/' + userId);
    notifyTypingReceive.subscribe(function(data) {
        console.log('typing', data);
        $('.chat-item[data-id="' + data.chat_id + '"]').addClass('chat-list-typing-active')
        if (typeof typingActiveChatList == 'undefined') {
            typingActiveChatList[data.chat_id] = null;
        }
        clearInterval(typingActiveChatList[data.chat_id]);
        typingActiveChatList[data.chat_id] = setTimeout(function() {
            $('.chat-item[data-id="' + data.chat_id + '"]').removeClass('chat-list-typing-active')
        }, 2000);
        if (data.chat_id == $('.chat-id').val()) {
            $('.chat-item-typing').removeClass('chat-item-typing-inactive')
            clearInterval(intRemoveTyping);
            intRemoveTyping = setTimeout(function() {
                $('.chat-item-typing').addClass('chat-item-typing-inactive')
                $('.chat-item[data-id="' + data.chat_id + '"]').removeClass('chat-list-typing-active')
            }, 2000);
        }
    });

    var notifyReadChat = new FireNotif();
    notifyReadChat.setKey(firebaseKey).setUrl(firebaseUrl)
    notifyReadChat.setPath('notify-read-chats/' + userId);
    notifyReadChat.subscribe(function(data) {
        $.each(data.ids, function(index, val) {
            $('.chat-item-user[data-message-id="' + val + '"]').find('.receiver-stat-icon').html(ccChat.CHAT_INDICATOR_READ_ICO);
            $('.chat-contact-message-wrapper .chat-item[data-id="' + data.chat_id + '"]').find('.receiver-stat-icon').html(ccChat.CHAT_INDICATOR_READ_ICO);
        });

    });

    $('.btn-chat-file').click(function(event) {
        var state = $(this).data('state');
        switch (state) {
            case 1:
            case undefined:
                showFeatureBottom();
                $(this).data('state', 2);
                break;
            case 2:
                hideFeatureBottom();
                $(this).data('state', 1);
                break;
        }
    });

    var intFindChat = null;
    $('.search-input').keyup(function(event) {
        clearInterval(intFindChat);
        intFindChat = setTimeout(function() {
            findChats()
        }, 1000);
        console.log($(this).val().length)
        if ($(this).val().length > 0) {
            $('.chat-contact-message-wrapper').parent().hide();
            $('.chat-search-message-wrapper').parent().show();
        } else {
            $('.chat-contact-message-wrapper').parent().show();
            $('.chat-search-message-wrapper').parent().hide();
        }
    }).val('');
    $('.search-contact').keyup(function(event) {
       var term = $(this).val();
       var wrapper = $('.chat-search-contact-wrapper ');

       wrapper.find('.chat-contact-list-item').hide();
       wrapper.find('.chat-contact-list-item:regex(data-user-fullname, .*'+term+'.*)').show();
       wrapper.find('.chat-contact-list-item:regex(data-user-email, .*'+term+'.*)').show();
       wrapper.find('.chat-contact-list-item:regex(data-user-username, .*'+term+'.*)').show();
    });
    $('body').on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        showFeatureBottom();
        $('.btn-chat-file').data('state', 2);
    })
    var params = {};
    params[csrf] = token;
    $('#chat_image_galery').fineUploader({
        template: 'qq-template-gallery',
        request: {
            endpoint: BASE_URL + '/administrator/chat/upload_image_file',
            params: params
        },
        deleteFile: {
            enabled: true,
            endpoint: BASE_URL + '/administrator/chat/delete_image_file',
        },
        thumbnails: {
            placeholders: {
                waitingPath: BASE_URL + '/asset/fine-upload/placeholders/waiting-generic.png',
                notAvailablePath: BASE_URL + '/asset/fine-upload/placeholders/not_available-generic.png'
            }
        },
        validation: {
            allowedExtensions: ["jpg", "jpeg", "png"],
            sizeLimit: 0,
            itemLimit: 5
        },
        showMessage: function(msg) {
            toastr['error'](msg);
        },
        callbacks: {
            onComplete: function(id, name, xhr) {
                if (xhr.success) {
                    var uuid = $('#chat_image_galery').fineUploader('getUuid', id);
                    $('#chat_image_galery_listed').append('<input type="hidden" class="listed_file_uuid" name="chat_image_uuid[' + id + ']" value="' + uuid + '" /><input type="hidden" class="listed_file_name" name="chat_image_name[' + id + ']" value="' + xhr.uploadName + '" />');
                } else {
                    toastr['error'](xhr.error);
                }
            },
            onDeleteComplete: function(id, xhr, isError) {
                if (isError == false) {
                    $('#chat_image_galery_listed').find('.listed_file_uuid[name="chat_image_uuid[' + id + ']"]').remove();
                    $('#chat_image_galery_listed').find('.listed_file_name[name="chat_image_name[' + id + ']"]').remove();
                }
            }
        }
    }); /*end image galery*/

    $('.chat-contact-detail .chat-history').scroll(function() {
        ccChat.bottomButtonMessage();
    })
    $('.btn-bottom-history').click(function(event) {
        event.preventDefault();
        $('.chat-contact-detail .chat-history').animate({
            'scrollTop': $('.chat-contact-detail .chat-history').prop('scrollHeight')
        }, 500)
        ccChat.bottomButtonMessage();
        ccChat.resetBadgeNotif();
    });

    $('.chat-history').on('scroll', function(){
      chatId = ccChat.getConversationUser().chatId;
      var scrollTop = $('.chat-contact-detail .chat-history').scrollTop();
      console.log(scrollTop);
      if (intLoadChat) {
        if (curScroll != null) {
          if (scrollTop <= 200 && curScroll > scrollTop) {
             intLoadChat = false;
             ccChat.loadMessage(chatId, ccChat.chatOffset, function(data) {
                console.log(data.messages.length)
                  if (data.total) {
                    $('.chat-contact-detail .chat-history').animate({scrollTop : 0}, 1)
                    $('.chat-contact-detail .chat-history').prepend(ccChat.parseMessages(data.messages))
                    ccChat.grouppingMessage();
                    ccChat.addOffset()
                  }
                  intLoadChat = true;
              });
           }
        }

        curScroll = scrollTop;
      }

    })

}); /*end doc ready*/
