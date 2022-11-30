import $ from 'jquery';

let token = $('meta[name="token"]').attr('content');
let query = '';

$.ajaxSetup({
    beforeSend: function (xhr)
    {
       xhr.setRequestHeader("X-AUTH-TOKEN", token);      
    }
});

// USER ACTIONS
    $(document).on("click",".accept", function (){
        let uuid = $(this).data('uuid');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/friends/' + uuid,
                type: 'POST',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(){
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukces',
                        text: 'Jesteście teraz znajomymi!'
                    })
                    getInvitationsList();
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się zaakceptować użytownika'
                    });
                }  
            });
        });
    });

    $(document).on("click",".cancel", function (){
        let uuid = $(this).data('uuid');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/invitations/' + uuid,
                type: 'DELETE',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(){
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukces',
                        text: 'Odrzucono zaproszenie!'
                    })
                    getInvitationsList();
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się odrzucić zaproszenia'
                    });
                }  
            });
        });
    });

    $(document).on("click",".remove", function (){
        let uuid = $(this).data('uuid');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/friends/' + uuid,
                type: 'DELETE',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(){
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukces',
                        text: 'Usunięto znajomego!'
                    })
                    getFriendList();
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Swal.fire({
                        icon: 'error',
                        title: 'Błąd',
                        text: 'Nie udało się usunąć znajomego'
                    })
                }  
            });
        });
    });
    $('#search_user').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            let uuid = $(this).val();
            
            $(document).ready(function(){
                $.ajax({
                    url: '/api/v1/users/' + uuid,
                    type: 'GET',
                    dataType: 'json',
                    async: true,
                    jsonp: true,
        
                    success: function(data){
                        showModalUserInfo(data.img, data.firstname, data.lastname, data.uuid, data.email, data.roles, data.friends, {'requestPath': 'search', 'isFriend': data.isFriend});
                    },
        
                    error : function(xhr, textStatus, errorThrown) {  
                        $(".modal").css("display", "flex");
                        $(".modal-content").css("padding", "50px");
                        $('.profile_image_user').css("display", "none");
                        $(".flname_user").css("display", "none");
                        $(".email_user").css("display", "none");
                        $(".friends_user").css("display", "none");
                        $(".s_info_user").css("display", "none");
                        $(".uuid_user").html('Nie znaleziono użytkownika!');
                    }  
                });
            });
        }
    });

// INVITATIONS ACTIONS

    $(document).on("click",".inf", function (){
        let uuid = $(this).data('uuid');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/invitations/' + uuid,
                type: 'GET',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(data){
                    let user = data.frm_user;
                    
                    showModalUserInfo(user.img, user.firstname, user.lastname, user.uuid, user.email, user.roles, user.friends, {'requestPath': 'inv', 'created': 'Otrzymano zaproszenie:</br>' + convertDateTime(data.created)});
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Swal.fire({
                        icon: 'error',
                        title: 'Błąd',
                        text: 'Nie udało się wyświetlić informacji o zaproszeniu',
                        footer: 'Błąd: ' + errorThrown
                    })
                }  
            });
        });
    });

    $(document).on("click",".send_inv", function (){
        let uuid = $(this).data('uuid');
    
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/invitations/' + uuid,
                type: 'POST',
                dataType: 'json',
                async: true,
                jsonp: true,
    
                success: function(){
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukces',
                        text: 'Wysłano zaproszenie'
                    });
                    $(".modal").css("display", "none");
                },
                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wysłać zaproszenia'
                    });
                }  
            });
        });
    });

// FRIENDS ACTIONS

    $(document).on("click",".friend_inf", function (){
        let uuid = $(this).data('uuid');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/friends/' + uuid,
                type: 'GET',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(data){
                    let user = data.sec_user;
                    
                    showModalUserInfo(user.img, user.firstname, user.lastname, user.uuid, user.email, user.roles, user.friends, {'requestPath': 'friends', 'created': 'Znajomość od:</br>' + convertDateTime(data.created)});
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Swal.fire({
                        icon: 'error',
                        title: 'Błąd',
                        text: 'Nie udało się wyświetlić informacji o użytkowniku',
                        footer: 'Błąd: ' + errorThrown
                    })
                }  
            });
        });
    });

    $(".search_button").click(function(){
        query = $('#szukaj_znajomego').val();
        getFriendList();
    });

    $('#szukaj_znajomego').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            query = $(this).val();
            getFriendList();
        }
    });

// MESSAGE ACTIONS

    $('#wiadomosc').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            let uuid = $(this).data('uuid');
            sendMessage(uuid);
        }
    });

    $(".send_button").click(function(){
        let uuid = $(this).data('uuid');
        sendMessage(uuid);
    });

    function isBlank(str) {
        return (!str || /^\s*$/.test(str));
    }


    function sendMessage(uuid){
        let text = $("#wiadomosc").val();

        if(isBlank(text)){
            Toast.fire({
                icon: 'error',
                title: 'Nie można wysłać pustej wiadomości'
            });
            return;
        }

        $("#wiadomosc").val('');
        
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/messages/' + uuid,
                type: 'POST',
                dataType: 'json',
                data: {
                    text: text
                },
                async: true,
                jsonp: false,

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wysłać wiadomości'
                    });
                }  
            });
        });
    }

    $(document).on("click",".remove_msg", function () {
        let id = $(this).data('id');
        
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/messages/' + id,
                type: 'DELETE',
                dataType: 'json',
                async: true,
                jsonp: false,

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się usunąć wiadomości'
                    });
                }  
            });
        });
    });

// THEME SWITCH

    $(".toggle-switch").click(function(){
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/theme',
                type: 'PATCH',
                dataType: 'json',
                async: true,
                jsonp: false,

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się zmienić motywu'
                    });
                }  
            });
        });
    });

// NOTIFICATIONS ACTION

    $(document).on("click",".remove_notification", function (){
        let id = $(this).data('id');

        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/notifications/' + id,
                type: 'DELETE',
                dataType: 'json',
                async: true,
                jsonp: false,

                success: function(){
                    getNotificationsList();
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się usunąć powiadomienia'
                    });
                }  
            });
        });
    });



function scrollToBottom(){
    $(".messages").animate({ scrollTop: $('.messages').prop("scrollHeight")}, 800);
}

let my_uuid = $('.uuid_text').html(),
    chat_uuid = new URLSearchParams(window.location.search).get('uuid'),
    last_data,
    html = '',
    last_timedate = '',
    st_html = '',
    friend_list = '',
    last_friend_list = '',
    invitations_list = '',
    last_invitations_list = '';

if(getCurrentPathName() == '/chat'){
    if(chat_uuid != null){
        getUserInfoChat();
    }else{
        $('.chat').css('display', 'none');
        $('.chat_text').css({
            'height': '100%',
            'display': 'flex',
            'justify-content': 'center',
            'align-items': 'center'
        });
        $('.chat_text h1').html('Rozpocznij czat wybierając znajomego!');

    }
}

function getUserInfoChat(){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/friends/' + chat_uuid,
            type: 'GET',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(data){
                let user = data.sec_user;

                $('.profile_image').attr('src', "/uploads/images/" + user.img);
                $(".flname").html(user.firstname + ' ' + user.lastname);
                $(".uuid").html('uuid: ' + user.uuid);
                $(".send_button").data('uuid', user.uuid);
                $(".friend_inf").data('uuid', user.uuid);
                $("#wiadomosc").data('uuid', user.uuid);
                chat_uuid = user.uuid; 

                if(user.roles == "ROLE_USER"){
                    $(".s_info").html('<span class="status" style="background:#f6b93b;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Użytkownik</span>');
                }else if(user.roles == "ROLE_MOD"){
                    $(".s_info").html('<span class="status" style="background:#26de81;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Moderator</span>');
                }else if(user.roles == "ROLE_ADMIN"){
                    $(".s_info").html('<span class="status" style="background:#e55039;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Administrator</span>');
                }else if(user == "ROLE_BLOCKED"){
                    $(".s_info").html('<span class="status" style="background:#a55eea;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Zablokowany</span>');    
                }

                getMessagesUser();
            },

            error : function(xhr, textStatus, errorThrown) {  
                $('.chat').css('display', 'none');
                $('.chat_text').css({
                    'height': '100%',
                    'display': 'flex',
                    'justify-content': 'center',
                    'align-items': 'center'
                });
                $('.chat_text h1').html('Nie odnaleziono użytkownika!');
            }  
        });
    });
}

// Function with interval to get chat from database
function getMessagesUser(){
    $('.loader').css('display', 'block');
    var getChat = setInterval(function() {
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/messages/' + chat_uuid,
                type: 'GET',
                dataType: 'json',
                async: true,
                jsonp: true,

                success: function(data){
                    
                    if(JSON.stringify(last_data) === JSON.stringify(data)){
                    }else{
                        if(isEmpty(data)){
                            $('.messages').html('<div class="details d_3">Brak czatu</div>');
                            html = '';
                        }else{
                            last_data = JSON.parse(JSON.stringify(data));
                            data.forEach(showChat);
                            $('.messages').html(html);
                            html = '';
                            last_timedate = '';
                            scrollToBottom();
                        }
                        
                    }
                },

                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wyświetlić wiadomości'
                    });
                }  
            });
        });
        
    }, 2000);
}

if(getCurrentPathName() == '/friends'){
    $('.loader').css('display', 'block');
    getFriendList();
    var getFriends = setInterval(function() {
        getFriendList();
    }, 5000);
}

function getFriendList(){
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/friends',
                type: 'GET',
                data: {
                    query: query,
                },
                dataType: 'json',
                async: true,
                jsonp: true,
    
                success: function(data){
                    $('.loader').css('display', 'none');
                    if(JSON.stringify(last_friend_list) === JSON.stringify(data)){
                    }else{
                        if(isEmpty(data)){
                            last_friend_list = JSON.parse(JSON.stringify(data));
                            friend_list = '<div class="card information">Nie znaleziono użytkowników!</div>';
                            $('.friends').html(friend_list);
                            friend_list = '';
                        }else{
                            last_friend_list = JSON.parse(JSON.stringify(data));
                            data.forEach(showFriends);
                            $('.friends').html(friend_list);
                            friend_list = '';
                        }
                        $('.f_1 .text').html('<h1>Znajomi (' + Object.keys(data).length + ')</h1>');
                        
                    }
                },
    
                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wyświetlić znajomych'
                    });
                }  
            });
        });
}

if(getCurrentPathName() == '/inv'){
    $('.loader').css('display', 'block');
    getInvitationsList();
    var getInvitations = setInterval(function() {
        getInvitationsList();
    }, 5000);
}

function getInvitationsList(){
        $(document).ready(function(){
            $.ajax({
                url: '/api/v1/invitations',
                type: 'GET',
                dataType: 'json',
                async: true,
                jsonp: true,
    
                success: function(data){
                    $('.loader').css('display', 'none');
                    if(JSON.stringify(last_invitations_list) === JSON.stringify(data)){
                    }else{
                        if(isEmpty(data)){
                            last_invitations_list = JSON.parse(JSON.stringify(data));
                            invitations_list = '<div class="card" style="justify-content:center;"><span class="last_message" style="text-align:center">Pusto :(</span></div>';
                            $('.invitations').html(invitations_list);
                            invitations_list = '';
                        }else{
                            last_invitations_list = JSON.parse(JSON.stringify(data));
                            data.forEach(showInvitations);
                            $('.invitations').html(invitations_list);
                            invitations_list = '';
                        }
                        $('.cards .inv').html('<h1>Otrzymane zaproszenia do znajomych (' + Object.keys(data).length + ')</h1>');
                        
                    }
                },
    
                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wyświetlić znajomych'
                    });
                }  
            });
        });
}

let notifications_list = '';
let last_notifications_list = '';

if(getCurrentPathName() == '/notifications'){
    $('.loader').css('display', 'block');
    getNotificationsList();
    var getInvitations = setInterval(function() {
        getNotificationsList();
    }, 5000);
}

function getNotificationsList(){
        $(document).ready(function(){
            notifications_list = '';
            $.ajax({
                url: '/api/v1/notifications',
                type: 'GET',
                dataType: 'json',
                async: true,
                jsonp: true,
    
                success: function(data){
                    $('.loader').css('display', 'none');
                    if(JSON.stringify(last_notifications_list) === JSON.stringify(data)){
                    }else{
                        if(isEmpty(data)){
                            last_notifications_list = JSON.parse(JSON.stringify(data));
                            notifications_list = '<div class="card notification" style="justify-content:center;"><span style="text-align:center">Pusto :(</span></div>';
                            $('.notifications').html(notifications_list);
                            notifications_list = '';
                        }else{
                            last_notifications_list = JSON.parse(JSON.stringify(data));
                            data.forEach(showNotifications);
                            $('.notifications').html(notifications_list);
                            notifications_list = '';
                        }
                        $('.cards .text').html('<h1>Otrzymane powiadomienia (' + Object.keys(data).length + ')</h1>');
                    }
                },
    
                error : function(xhr, textStatus, errorThrown) {  
                    Toast.fire({
                        icon: 'error',
                        title: 'Nie udało się wyświetlić znajomych'
                    });
                }  
            });
        });
}

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

function showNotifications(item){
    item = JSON.parse(JSON.stringify(item));

    notifications_list += '';
    notifications_list += '<div class="card notification"><span>';
    if(item.status == 1){
        notifications_list += '<span class="status" style="background:#2ed573;color:#ffffff;padding:5px;border-radius:5px;">Sukces</span>';
    }else if(item.status == 2){
        notifications_list += '<span class="status" style="background:#ff4757;color:#ffffff;padding:5px;border-radius:5px;">Odrzucone</span>';
    }else if(item.status == 3){
        notifications_list += '<span class="status" style="background:#ff4757;color:#ffffff;padding:5px;border-radius:5px;">Usunięte</span>';
    }else if(item.status == 4){
        notifications_list += '<span class="status" style="background:#45aaf2;color:#ffffff;padding:5px;border-radius:5px;">Informacja</span>';
    }else if(item.status == 5){
        notifications_list += '<span class="status" style="background:#f6b93b;color:#ffffff;padding:5px;border-radius:5px;">Ostrzeżenie</span>';
    }

    notifications_list += '</span>';
    notifications_list += '<span>' + item.subject + '</span>';
    notifications_list += '<span>' + item.text + '</span>';
    notifications_list += '<span>' + convertDateTime(item.created) + '</span>';
    notifications_list += '<span class="links">';
    notifications_list += '<a class="remove_notification" data-id="' + item.id + '"><i class="fa-solid fa-trash"></i></a></span></div>';
}
// Function to render invitations list
function showInvitations(item){
    item = JSON.parse(JSON.stringify(item));

    invitations_list += '<div class="card"><span class="image"><img src="/uploads/images/' + item.frm_user.img + '"></span>';
    invitations_list += '<div class="info">';
    invitations_list += '<span style="text-align:center;">' + item.frm_user.firstname + ' ' + item.frm_user.lastname + '</span>';

    if(item.frm_user.roles == "ROLE_USER"){
        friend_list += '<span class="status" style="background:#f6b93b;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Użytkownik</span>';
    }else if(item.frm_user.roles == "ROLE_MOD"){
        friend_list += '<span class="status" style="background:#26de81;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Moderator</span>';
    }else if(item.frm_user.roles == "ROLE_ADMIN"){
        friend_list += '<span class="status" style="background:#e55039;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Administrator</span>';
    }else if(item.frm_user.roles == "ROLE_BLOCKED"){
        friend_list += '<span class="status" style="background:#a55eea;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Zablokowany</span>';    
    }

    invitations_list += '</div><span class="last_message">' + convertDateTime(item.created) + '</span>';
    invitations_list += '<span class="links">';
    invitations_list += '<a class="accept" data-uuid="'+ item.frm_user.uuid +'"><i class="bx bxs-check-square icon"></i></a>';
    invitations_list += '<a class="cancel" data-uuid="'+ item.frm_user.uuid +'"><i class="bx bxs-user-minus icon"></i></a>';
    invitations_list += '<a class="inf" data-uuid="'+ item.frm_user.uuid +'"><i class="bx bxs-info-circle icon" ></i></a></span></div>';
}
// Function to render friends list
function showFriends(item){
    item = JSON.parse(JSON.stringify(item));
    
    let user = item.user.sec_user;

    friend_list += '<div class="card" id="' + user.uuid + '">';
    let status = user.status;
    if(status == 0){
        status = '';
    }else{
        status = 'active';
    }
    friend_list += '<span class="image"><img src="/uploads/images/' + user.img + '"><span class="status ' + status + '"><i class="fa-solid fa-circle"></i></span></span>';
    friend_list += '<div class="info">';    
    friend_list += '<span style="text-align:center;">' + user.firstname + ' ' + user.lastname + '</span>';

    if(user.roles == "ROLE_USER"){
        friend_list += '<span class="status" style="background:#f6b93b;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Użytkownik</span>';
    }else if(user.roles == "ROLE_MOD"){
        friend_list += '<span class="status" style="background:#26de81;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Moderator</span>';
    }else if(user.roles == "ROLE_ADMIN"){
        friend_list += '<span class="status" style="background:#e55039;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Administrator</span>';
    }else if(user.roles == "ROLE_BLOCKED"){
        friend_list += '<span class="status" style="background:#a55eea;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Zablokowany</span>';    
    }

    friend_list += '</div><span class="last_message">';
    let g_id = $('input[name="8M52PFLzTJ"]').val();

    let str = item.msg;
    if(str != null){
        
        if(str.length > 18){
            str = str.slice(0, 18) + '...';
            if(str.includes('<i>')){
                str += '</i>';
            }
        }

        if(item.u_id == g_id){
            friend_list += 'Ty: ' + str + ' ';
        }else{
            if(item.status > 1){
                friend_list += str + ' ';
            }else{
                friend_list += '<p class="new_message">' + str + '</p> ';
            }
        }

    }else{
        friend_list += 'Brak wiadomości';
    }

    if(item.status != null && item.u_id == g_id){
        if(item.status == 0){
            friend_list += '<i class="fa-solid fa-check"></i>';
        }else if(item.status == 1){
            friend_list += '<i class="fa-solid fa-check-double"></i>';
        }else if(item.status == 2){
            friend_list += '<i class="fa-solid fa-circle-check"></i>';
        }
           
    }

    friend_list += '</span><span class="links">';
    friend_list += '<a href="chat?uuid=' + user.uuid + '" class="b_chat"><i class="bx bxs-message icon"></i></a><a class="remove" data-uuid="' + user.uuid + '"><i class="bx bxs-user-minus icon"></i></a><a class="friend_inf" data-uuid="' + user.uuid + '"><i class="bx bxs-info-circle icon"></i></a></span></div>';
}
// Fucntion to Render Chat
function showChat(item, index){
    item = JSON.parse(JSON.stringify(item));

    let date = item.created;

    date = convertDateTime(date);
    
    let time = date.split(" ");
    let today = getCurrentDate();

    if(last_timedate != time[0]){
        if(time[0] == today){
            html += '<div class="details d_3">Dziś</div>';
        }else{
            html += '<div class="details d_3">' + time[0] + '</div>';
        }
        last_timedate = time[0];
    }

    if(item.outgoing_msg.uuid == my_uuid){
        let status = item.status;

        if(status == 0){
            st_html = '<i class="fa-solid fa-check"></i>';
        }else if(status == 1){
            st_html = '<i class="fa-solid fa-check-double"></i>';
        }else if(status == 2){
            st_html = '<i class="fa-solid fa-circle-check"></i>';
        }

        html += '<span class="details d_1"><img src="/uploads/images/' + item.outgoing_msg.img + '"><span class="out"><p>' + item.msg + '</p><span class="time">' + time[1] + ' ' + st_html + ' </span></span><a class="remove_msg" data-id="' + item.id + '"><i class="fa-solid fa-trash"></i></a></span>';
    }else{
        html += '<span class="details d_2"><img src="/uploads/images/' + item.outgoing_msg.img + '"><span class="in"><p>' + item.msg + '</p><span class="time">' + time[1] + ' </span></span></span>';
    }

}

// UPDATE USER DATA

    $('.modal-account button[type="submit"]').click(function (){
        let pass1 = $('.modal-account #account_update_form_plainPassword').val();
        let pass2 = $('.modal-account .input-field input[type="password"]').val();

        if(pass1 == null || pass1 == '' && pass2 == null || pass2 == ''){
            $('form[name="account_update_form"]').submit();
        }else{
            if(pass1.length >= 6 && pass2.length >= 6){
                if(pass1 == pass2){
                    $('form[name="account_update_form"]').submit();
                }else{
                    $('.modal-account .error').html('<p style="color: #eb4d4b; text-align: center; font-weight: 600;">Hasła się nie zgadzają!</p>')
                }
            }else{
                $('.modal-account .error').html('<p style="color: #eb4d4b; text-align: center; font-weight: 600;">Hasło jest za krótkie!</p>')
            }
        }
    });

// SOME FUNCTIONS

    function getCurrentDate(){
        const d = new Date();
        let month = '';
        
        if(d.getMonth()+1 < 10){
            month = '0' + (d.getMonth()+1);
        }else{
            month = d.getMonth() + 1;
        }
        return d.getDate() + '.' + month + '.' + d.getFullYear();
    }
    function convertDate(date){
        const d = new Date(date);
        let month = '';
        
        if(d.getMonth()+1 < 10){
            month = '0' + (d.getMonth()+1);
        }else{
            month = d.getMonth() + 1;
        }
        return d.getDate() + '.' + month + '.' + d.getFullYear();
    }
    function convertDateTime(date){
        var tzoffset = (new Date()).getTimezoneOffset() * 60000;
    
        date = (new Date(Date.parse(date) - tzoffset)).toISOString().slice(0, -1).replace('T', ' ');
        date = date.slice(0, -7)

        let time = date.split(" ");
    
        time[0] = convertDate(time[0]);
    
        return time[0] + ' ' + time[1];
    }
    // Function to check if Object( JSON ) is empty
    function isEmpty(obj) {
        return Object.keys(obj).length === 0;
    }

    // Function to return Current Path Name
    function getCurrentPathName () {
        return window.location.pathname;
    }

    /**
     * Function to show user info
     * @param img Image path
     * @param firstname Firstname of user
     * @param lastname Lastname of user
     * @param uuid Unique identificator
     * @param email Email
     * @param roles Array
     * @param friends String
     * @param moreinfo Array {'requestPath': 'path', 'isFriend': 'boolean', 'created': 'String with date'}, path: friends, inv, search
     */
    function showModalUserInfo(img, firstname, lastname, uuid, email, roles, friends, moreinfo){
        $(".modal-content").css("padding", "20px");
        $('.profile_image_user').css("display", "block");
        $(".flname_user").css("display", "block");
        $(".email_user").css("display", "block");
        $(".friends_user").css("display", "block");
        $(".s_info_user").css("display", "block");

        // 
        $('.profile_image_user').attr('src', "/uploads/images/" + img);
        $(".flname_user").html(firstname + ' ' + lastname);
        $(".uuid_user").html('uuid: ' + uuid);
        $(".email_user").html(email);
        
        if(moreinfo['requestPath']){
            if(moreinfo['requestPath'] == 'friends'){
                $(".buttons").html("<a class='b_chat' href='chat?uuid=" + uuid + "'>Czatuj <i class='bx bxs-check-square icon'></i></a> <a class='remove' data-uuid='" + uuid + "'>Usuń <i class='bx bxs-user-minus icon'></i></a> <a class='close_info_user'>Zamknij <i class='fa-solid fa-xmark'></i></a>");
            }
            if(moreinfo['requestPath'] == 'search'){
                if(uuid == my_uuid){
                    $(".buttons").html("<a class='close_info_user'>Zamknij <i class='fa-solid fa-xmark'></i></a>");
                }else{
                    if(moreinfo['isFriend'] == true){
                        $(".buttons").html("<a class='b_chat' href='chat?uuid=" + uuid + "'>Czatuj <i class='bx bxs-check-square icon'></i></a> <a class='remove' data-uuid='" + uuid + "'>Usuń <i class='bx bxs-user-minus icon'></i></a> <a class='close_info_user'>Zamknij <i class='fa-solid fa-xmark'></i></a>");
                    }else{
                        $(".buttons").html("<a class='send_inv' data-uuid='" + uuid + "'>Dodaj <i class='bx bxs-check-square icon'></i></a> <a class='close_info_user'>Zamknij <i class='fa-solid fa-xmark'></i></a>");
                    }
                }
            }
            if(moreinfo['requestPath'] == 'inv'){
                $(".buttons").html("<a class='accept a_info' data-uuid='" + uuid + "'>Akceptuj <i class='bx bxs-check-square icon'></i></a> <a class='cancel c_info' data-uuid='" + uuid + "'>Odrzuć <i class='bx bxs-user-minus icon'></i></a> <a class='close_info_user'>Zamknij <i class='fa-solid fa-xmark'></i></a>");
            }
        }
        
        if(roles[0] == "ROLE_USER"){
            $(".s_info_user").html('<span class="status" style="background:#f6b93b;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Użytkownik</span>');
        }else if(roles[0] == "ROLE_MOD"){
            $(".s_info_user").html('<span class="status" style="background:#26de81;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Moderator</span>');
        }else if(roles[0] == "ROLE_ADMIN"){
            $(".s_info_user").html('<span class="status" style="background:#e55039;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Administrator</span>');
        }else if(roles[0] == "ROLE_BLOCKED"){
            $(".s_info_user").html('<span class="status" style="background:#a55eea;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Zablokowany</span>');     
        }

        if(moreinfo['created']){
            $('.friends_user').html(moreinfo['created'] + '</br><i class="bx bxs-user-detail icon"></i> Znajomi: ' + friends);
        }else{
            $('.friends_user').html('<i class="bx bxs-user-detail icon"></i> Znajomi: ' + friends);
        }
        
        $(".modal").css("display", "flex");
    }

// Some Rendering Code

    $(document).on("click",".close_info_user", function (){
        $(".modal").css("display", "none");
    });

    $(window).click(function(e) {
        if(e.target.className == 'modal'){
            $(".modal").css("display", "none");
        }
    });

    $(".close_info").click(function(){
        $(".more_info").css("display", "none");
        $(".cards").css("width", "");
        $(".home").css("display", "");
    });