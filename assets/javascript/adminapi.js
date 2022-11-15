import $ from 'jquery';
import 'select2'; 
let token = $('meta[name="token"]').attr('content');

$(() => {
    $('.select2-enable').select2();
});

$(document).ready(function (){
    $.ajaxSetup({
        headers: {
            "X-AUTH-TOKEN": token
        }  
    });
});

let limit = 20,
    user_list = '',
    invitations_list = '',
    friends_list = '',
    notifications_list = '',
    messages_list = '',
    query = '',
    role = 'default';

if(getCurrentPathName() == '/admin/invitations'){
    $(document).ready(function (){
        getInvitations('Wszyscy', 'Wszyscy');
    });
}

$("#search_invitations").click(function(){
    $(document).ready(function (){
        $('.body_invitations_list').html('');
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getInvitations(from_user, to_user);
    });
});

if(getCurrentPathName() == '/admin/users'){
    $(document).ready(function (){
        getUsers();
    }
)};

$("#search_users").click(function(){
    $(document).ready(function (){
        query = $("input[name='query']").val();
        role =  $("select[name='role']").val();
        $('.body_user_list').html('');
        getUsers();
    });
});

if(getCurrentPathName() == '/admin/friends'){
    $(document).ready(function (){
        getFriends('Wszyscy', 'Wszyscy');
    }
)};

$("#search_friends").click(function(){
    $(document).ready(function (){
        let user = $('select[name="user_search"]').val();
        let friend =  $('select[name="friend_search"]').val();
        $('.body_friends_list').html('');
        getFriends(user, friend);
    });
});

if(getCurrentPathName() == '/admin/notifications'){
    $(document).ready(function (){
        getNotifications('Wszyscy');
    }
)};

$("#search_notifications").click(function(){
    $(document).ready(function (){
        $('.body_notifications_list').html('');
        let to_user = $("#to_user").val();
        getNotifications(to_user);
    });
});

if(getCurrentPathName() == '/admin/messages'){
    $(document).ready(function (){
        getMessages('Wszyscy', 'Wszyscy');
    }
)};

$("#search_messages").click(function(){
    $(document).ready(function (){
        $('.body_messages_list').html('');
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getMessages(from_user, to_user);
    });
});

function getUsers(){
    user_list = '';
    $.ajax({
        url: '/api/v1/admin/users',
        type: 'GET',
        dataType: 'json',
        data: {
            query: query,
            role: role,
            limit: limit
        },
        async: true,
        jsonp: false,
        
        beforeSend: function(){
            $('.loader').css('display', 'block');
        },

        success: function(data){
            if(isEmpty(data)){
                $('.loader').css('display', 'none');
                $('.body_user_list').html('<tr><td colspan="8" data-label="Informacja">Brak użytkowników</td></tr>');
            }else{
                var length = Object.keys(data).length;
                user_list += '<tr><td colspan="8">Wyświetlanie rekodów: ' + length + ', Limit (' + (limit != 'all' ? limit + ' rekordów' : 'Wszystkie rekordy') + ') <span class="more_records r_20">20</span> <span class="more_records r_50">50</span> <span  class="more_records r_100">100</span> <span  class="more_records r_all">Wszystko</span>';
                if(length > limit){
                    user_list += '. Wyświetlanie maks. ' + limit + ' rekordów</td></tr>';
                }
                data.forEach(showUsers);
                $('.loader').css('display', 'none');
                $('.body_user_list').html(user_list);
            }
            
        },

        error : function(xhr, textStatus, errorThrown) {  
            Swal.fire({
                icon: 'error',
                title: 'Błąd',
                text: 'Nie udało się wyświetlić użytkowników',
                footer: 'Błąd: ' + errorThrown 
            })
        }  
    });
}


function showUsers(user){
    user = JSON.parse(JSON.stringify(user));
    
    user_list += '<tr>';

    user_list += '<td data-label="IMG"><img src="/uploads/images/' + user.img + '"></td>';
    user_list += '<td data-label="Imię">' + user.firstname + '</td>';
    user_list += '<td data-label="Nazwisko">' + user.lastname + '</td>';
    user_list += '<td data-label="Email">' + user.email + '</td>';
    user_list += '<td data-label="Rola">';

    if(user.roles == "ROLE_USER"){
        user_list += '<span class="status" style="background:#f6b93b;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Użytkownik</span>';
    }else if(user.roles == "ROLE_MOD"){
        user_list += '<span class="status" style="background:#26de81;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Moderator</span>';
    }else if(user.roles == "ROLE_ADMIN"){
        user_list += '<span class="status" style="background:#e55039;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Administrator</span>';
    }else if(user.roles == "ROLE_BLOCKED"){
        user_list += '<span class="status" style="background:#a55eea;color:#ffffff;padding:0 5px 0 5px;border-radius:5px;">Zablokowany</span>';
    }
    
    user_list += '</td><td data-label="Uuid">' + user.uuid + '</td>';
    user_list += '<td data-label="Znajomi">' + user.friends + '</td>';
    user_list += '<td data-label="Akcje">';                                 
    user_list += '<a class="user_delete" data-id="' + user.id + '"><i class="fa-solid fa-user-minus"></i></a>';
    user_list += '<a class="user_lock" data-id="' + user.id + '"><i class="fa-solid fa-lock"></i></a>';
    user_list += '<a class="user_edit" data-id="' + user.id + '"><i class="fa-solid fa-pen-to-square"></i></a></td>';

    user_list += '</tr>';
}

function getInvitations(from, to){
    invitations_list = '';
    $.ajax({
        url: '/api/v1/admin/invitations',
        type: 'GET',
        dataType: 'json',
        data: {
            from: from,
            to: to,
            limit: limit
        },
        async: true,
        jsonp: false,
        
        beforeSend: function(){
            $('.loader').css('display', 'block');
        },

        success: function(data){
            if(isEmpty(data)){
                $('.loader').css('display', 'none');
                $('.body_invitations_list').html('<tr><td colspan="4" data-label="Informacja">Brak zaproszeń</td></tr>');
            }
            else{
                var length = Object.keys(data).length;
                invitations_list += '<tr><td colspan="4">Wyświetlanie rekodów: ' + length + ', Limit (' + (limit != 'all' ? limit + ' rekordów' : 'Wszystkie rekordy') + ') <div class="more_records r_20">20</div> <div class="more_records r_50">50</div> <div class="more_records r_100">100</div> <div class="more_records r_all">Wszystko</div>';
                if(length > limit){
                    invitations_list += '. Wyświetlanie maks. ' + limit + ' rekordów</td></tr>';
                }
                data.forEach(showInvitations);
                $('.loader').css('display', 'none');
                $('.body_invitations_list').html(invitations_list);
            }
            
        },

        error : function(xhr, textStatus, errorThrown) {  
            Swal.fire({
                icon: 'error',
                title: 'Błąd',
                text: 'Nie udało się wyświetlić zaproszeń',
                footer: 'Błąd: ' + errorThrown 
            })
        }  
    });
}

function showInvitations(inv){
    inv = JSON.parse(JSON.stringify(inv));

    invitations_list += '<tr>';
    invitations_list += '<td data-label="Od użytkownika"><span><img src="/uploads/images/' + inv.frmUser.img + '"> ' + inv.frmUser.firstname + ' ' + inv.frmUser.lastname + ' ' + inv.frmUser.email + '</span></td>';
    invitations_list += '<td data-label="Do użytkownika"><span><img src="/uploads/images/' + inv.toUser.img + '"> ' + inv.toUser.firstname + ' ' + inv.toUser.lastname + ' ' + inv.toUser.email + '</span></td>';
    invitations_list += '<td data-label="Wysłano">' + convertDateTime(inv.created) + '</td>';
    invitations_list += '<td data-label="Akcje"><a class="invitation_delete" data-id="' + inv.id + '"><i class="fa-solid fa-trash"></i></a><a class="invitation_edit" data-id="' + inv.id + '"><i class="fa-solid fa-pen-to-square"></i></a></td>';
    invitations_list += '</tr>';
}

function getFriends(user, friend){
    friends_list = '';
    $.ajax({
        url: '/api/v1/admin/friends',
        type: 'GET',
        dataType: 'json',
        data: {
            user: user,
            friend: friend,
            limit: limit
        },
        async: true,
        jsonp: false,
        
        beforeSend: function(){
            $('.loader').css('display', 'block');
        },

        success: function(data){
            if(isEmpty(data)){
                $('.loader').css('display', 'none');
                $('.body_friends_list').html('<tr><td colspan="4" data-label="Informacja">Brak znajomych</td></tr>');
            }
            else{
                var length = Object.keys(data).length;
                friends_list += '<tr><td colspan="4">Wyświetlanie rekodów: ' + length + ', Limit (' + (limit != 'all' ? limit + ' rekordów' : 'Wszystkie rekordy') + ') <div class="more_records r_20">20</div> <div class="more_records r_50">50</div> <div class="more_records r_100">100</div> <div class="more_records r_all">Wszystko</div>';
                if(length > limit){
                    friends_list += '. Wyświetlanie maks. ' + limit + ' rekordów</td></tr>';
                }
                data.forEach(showFriends);
                $('.loader').css('display', 'none');
                $('.body_friends_list').html(friends_list);
            }
            
        },

        error : function(xhr, textStatus, errorThrown) {  
            Swal.fire({
                icon: 'error',
                title: 'Błąd',
                text: 'Nie udało się wyświetlić znajomych',
                footer: 'Błąd: ' + errorThrown 
            })
        }  
    });
}

function showFriends(friend){
    friend = JSON.parse(JSON.stringify(friend));

    friends_list += '<tr>';
    friends_list += '<td data-label="Użytkownik"><span><img src="/uploads/images/' + friend.user.img + '"> ' + friend.user.firstname + ' ' + friend.user.lastname + ' ' + friend.user.email + '</span></td>';
    friends_list += '<td data-label="Znajomy"><span><img src="/uploads/images/' + friend.secUser.img + '"> ' + friend.secUser.firstname + ' ' + friend.secUser.lastname + ' ' + friend.secUser.email + '</span></td>';
    friends_list += '<td data-label="Od">' + convertDateTime(friend.created) + '</td>';
    friends_list += '<td data-label="Akcje"><a class="friend_delete" data-id="' + friend.id + '"><i class="fa-solid fa-trash"></i></a><a class="friend_edit" data-id="' + friend.id + '"><i class="fa-solid fa-pen-to-square"></i></a></td>';
    friends_list += '</tr>';
}

function getNotifications(user){
    notifications_list = '';
    $.ajax({
        url: '/api/v1/admin/notifications',
        type: 'GET',
        dataType: 'json',
        data: {
            user: user,
            limit: limit
        },
        async: true,
        jsonp: false,
        
        beforeSend: function(){
            $('.loader').css('display', 'block');
        },

        success: function(data){
            if(isEmpty(data)){
                $('.loader').css('display', 'none');
                $('.body_notifications_list').html('<tr><td colspan="7" data-label="Informacja">Brak powiadomień</td></tr>');
            }
            else{
                var length = Object.keys(data).length;
                notifications_list += '<tr><td colspan="7">Wyświetlanie rekodów: ' + length + ', Limit (' + (limit != 'all' ? limit + ' rekordów' : 'Wszystkie rekordy') + ') <div class="more_records r_20">20</div> <div class="more_records r_50">50</div> <div class="more_records r_100">100</div> <div class="more_records r_all">Wszystko</div>';
                if(length > limit){
                    notifications_list += '. Wyświetlanie maks. ' + limit + ' rekordów</td></tr>';
                }
                data.forEach(showNotifications);
                $('.loader').css('display', 'none');
                $('.body_notifications_list').html(notifications_list);
            }
            
        },

        error : function(xhr, textStatus, errorThrown) {  
            Swal.fire({
                icon: 'error',
                title: 'Błąd',
                text: 'Nie udało się wyświetlić powiadomień',
                footer: 'Błąd: ' + errorThrown 
            })
        }  
    });
}

function showNotifications(notif){
    notif = JSON.parse(JSON.stringify(notif));
               
    notifications_list += '<tr><td data-label="Użytkownik"><span><img src="/uploads/images/' + notif.user.img + '"> ' + notif.user.firstname + ' ' + notif.user.lastname + ' ' + notif.user.email + '</span></td>';
    
    if(notif.status == 1){
        notifications_list += '<td data-label="Status"><span class="status" style="background:#2ed573;color:#ffffff;padding:5px;border-radius:5px;">Sukces</span></td>';
    }else if(notif.status == 2){
        notifications_list += '<td data-label="Status"><span class="status" style="background:#ff4757;color:#ffffff;padding:5px;border-radius:5px;">Odrzucone</span></td>';
    }else if(notif.status == 3){
        notifications_list += '<td data-label="Status"><span class="status" style="background:#ff4757;color:#ffffff;padding:5px;border-radius:5px;">Usunięte</span></td>';
    }else if(notif.status == 4){
        notifications_list += '<td data-label="Status"><span class="status" style="background:#45aaf2;color:#ffffff;padding:5px;border-radius:5px;">Informacja</span></td>';
    }else if(notif.status == 5){
        notifications_list += '<td data-label="Status"><span class="status" style="background:#f6b93b;color:#ffffff;padding:5px;border-radius:5px;">Ostrzeżenie</span></td>';
    }

    notifications_list += '<td data-label="Temat">' + notif.subject + '</td>';
    notifications_list += '<td data-label="Tekst">' + notif.text + '</td>';
    notifications_list += '<td data-label="Odczytane">' + (notif.opened == false ? 'Nie' : 'Tak') + '</td>';
    notifications_list += '<td data-label="Wysłano">' + convertDateTime(notif.created) + '</td>';
    notifications_list += '<td data-label="Akcje"><a class="notification_delete" data-id="' + notif.id + '"><i class="fa-solid fa-trash"></i></a><a class="notification_edit" data-id="' + notif.id + '"><i class="fa-solid fa-pen-to-square"></i></a></td></tr>';
}

function getMessages(from, to){
    messages_list = '';
    $.ajax({
        url: '/api/v1/admin/messages',
        type: 'GET',
        dataType: 'json',
        data: {
            from: from,
            to: to,
            limit: limit
        },
        async: true,
        jsonp: false,
        
        beforeSend: function(){
            $('.loader').css('display', 'block');
        },

        success: function(data){
            if(isEmpty(data)){
                $('.loader').css('display', 'none');
                $('.body_messages_list').html('<tr><td colspan="4" data-label="Informacja">Brak wiadomości</td></tr>');
            }
            else{
                var length = Object.keys(data).length;
                messages_list += '<tr><td colspan="4">Wyświetlanie rekodów: ' + length + ', Limit (' + (limit != 'all' ? limit + ' rekordów' : 'Wszystkie rekordy') + ') <div class="more_records r_20">20</div> <div class="more_records r_50">50</div> <div class="more_records r_100">100</div> <div class="more_records r_all">Wszystko</div>';
                if(length > limit){
                    messages_list += '. Wyświetlanie maks. ' + limit + ' rekordów</td></tr>';
                }
                data.forEach(showMessages);
                $('.loader').css('display', 'none');
                $('.body_messages_list').html(messages_list);
            }
            
        },

        error : function(xhr, textStatus, errorThrown) {  
            Swal.fire({
                icon: 'error',
                title: 'Błąd',
                text: 'Nie udało się wyświetlić wiadomości',
                footer: 'Błąd: ' + errorThrown 
            })
        }  
    });
}

function showMessages(mess){
    mess = JSON.parse(JSON.stringify(mess));
            
    messages_list += '<tr><td data-label="Użytkownik 1"><span><img src="/uploads/images/' + mess[0].outgoingMsg.img + '"> ' + mess[0].outgoingMsg.firstname + ' ' + mess[0].outgoingMsg.lastname + ' ' + mess[0].outgoingMsg.email + '</span></td>';
    messages_list += '<td data-label="Użytkownik 2"><span><img src="/uploads/images/' + mess[0].incomingMsg.img + '"> ' + mess[0].incomingMsg.firstname + ' ' + mess[0].incomingMsg.lastname + ' ' + mess[0].incomingMsg.email + '</span></td>';
    messages_list += '<td data-label="Ilość">' + mess.Count + '</td>';
    messages_list += '<td data-label="Akcje"><a class="messages_clear" data-fid="' + mess[0].outgoingMsg.id + '" data-sid="' + mess[0].incomingMsg.id + '"><i class="fa-solid fa-broom"></i></a></td></tr>';
}

// ACTIONS USERS API

$(document).on("click",".user_delete", function () {
    let id = $(this).data('id');
    
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/users/' + id,
            type: 'DELETE',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(){
                query = $("input[name='query']").val();
                role =  $("select[name='role']").val();
                $('.body_user_list').html('');
                getUsers();
            },
            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się usunąć użytkownika',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
});

$(document).on("click",".user_edit", function () {
    let id = $(this).data('id');
    
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/users/' + id,
            type: 'GET',
            dataType: 'json',
            async: true,
            jsonp: true,

            success: function (data){
                $('.admin-modal').css('display', 'flex');

                $('.profile_image').attr('src', '/uploads/images/' + data.img);
                $('input[name="user_id"]').val(data.id);
                $('input[name="firstname"]').val(data.firstname);
                $('input[name="lastname"]').val(data.lastname);
                $('input[name="uuid"]').val(data.uuid);
                $('input[name="email"]').val(data.email);

                if(data.roles == "ROLE_USER"){
                    $('select[name="n_role"]').val('user');
                }else if(data.roles == "ROLE_MOD"){
                    $('select[name="n_role"]').val('mod');
                }else if(data.roles == "ROLE_ADMIN"){
                    $('select[name="n_role"]').val('admin');
                }

                $('input[name="friends"]').val(data.friends);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się pobrać użytkownika',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
});

$(".edit_modal_user").click(function(){
    let id = $('input[name="user_id"]').val();
    let data = $('#edit_user_form').serialize();
    editUser(id, data);
});

$(document).on("click",".user_lock", function () {
    let id = $(this).data('id');
    let data = {
        n_role: 'block'
    }
    editUser(id, data);
});

function editUser(id, data){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/users/' + id,
            type: 'PUT',
            dataType: 'json',
            data: data,
            async: true,
            jsonp: true,

            success: function (){
                query = $("input[name='query']").val();
                role =  $("select[name='role']").val();
                $('.body_user_list').html('');
                $('.admin-modal').css('display', 'none');
                getUsers();
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się edytować użytkownika',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

// ACTIONS INVITATIONS API

$(document).on("click",".invitation_delete", function () {
    let id = $(this).data('id');
    
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/invitations/' + id,
            type: 'DELETE',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(){
                $('.body_invitations_list').html('');
                let from_user = $("#from_user").val();
                let to_user = $("#to_user").val();
                getInvitations(from_user, to_user);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się usunąć zaproszenia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
});

$(document).on("click",".invitation_edit", function () {
    let id = $(this).data('id');
    getInvitation(id);
});

function getInvitation(id){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/invitations/' + id,
            type: 'GET',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(data){
                $('.admin-modal').css('display', 'flex');
                changeSelect2(data.frmUser.id, data.toUser.id);
                $('input[name="send"]').val(convertDateTime(data.created));
                $('input[name="inv_id"]').val(data.id);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się pobrać zaproszenia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

$(".edit_modal_inv").click(function(){
    let id = $('input[name="inv_id"]').val();
    let data = $('#edit_invitation_form').serialize();
    editInvitation(id, data);
});

function editInvitation(id, data){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/invitations/' + id,
            type: 'PUT',
            dataType: 'json',
            data: data,
            async: true,
            jsonp: false,

            success: function(data){
                $('.body_invitations_list').html('');
                let from_user = $("#from_user").val();
                let to_user = $("#to_user").val();
                getInvitations(from_user, to_user);

                $('.admin-modal').css('display', 'none');
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się edytować zaproszenia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

// ACTIONS FRIENDS API

$(document).on("click",".friend_edit", function () {
    let id = $(this).data('id');
    getFriend(id);
});

function getFriend(id){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/friends/' + id,
            type: 'GET',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(data){
                $('.admin-modal').css('display', 'flex');
                changeSelect2(data.user.id, data.secUser.id);
                $('input[name="from"]').val(convertDateTime(data.created));
                $('input[name="friend_id"]').val(data.id);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się pobrać znajomego',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

$(".edit_modal_friend").click(function(){
    let id = $('input[name="friend_id"]').val();
    let data = $('#edit_friend_form').serialize();
    editFriend(id, data);
});

function editFriend(id, data){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/friends/' + id,
            type: 'PUT',
            dataType: 'json',
            data: data,
            async: true,
            jsonp: false,

            success: function(data){
                $('.body_friends_list').html('');
                let user = $("#user").val();
                let friend = $("#friend").val();
                getFriends(user, friend);

                $('.admin-modal').css('display', 'none');
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się edytować znajomego',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

$(document).on("click",".friend_delete", function () {
    let id = $(this).data('id');
    
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/friends/' + id,
            type: 'DELETE',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(){
                $('.body_friends_list').html('');
                let user = $('select[name="user_search"]').val();
                let friend =  $('select[name="friend_search"]').val();
                getFriends(user, friend);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się usunąć znajomego',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
});

// ACTIONS NOTIFICATIONS API

$(document).on("click",".notification_delete", function () {
    let id = $(this).data('id');
    
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/notifications/' + id,
            type: 'DELETE',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(){
                $('.body_notifications_list').html('');
                let to_user = $("#to_user_modal").val();
                getNotifications(to_user);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się usunąć powiadomienia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
});

$(document).on("click",".notification_edit", function () {
    let id = $(this).data('id');
    getNotification(id);
});

function getNotification(id){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/notifications/' + id,
            type: 'GET',
            dataType: 'json',
            async: true,
            jsonp: false,

            success: function(data){
                $('.admin-modal').css('display', 'flex');
                changeSelect2(data.user.id);
                $('select[name="status"]').val(data.status);
                $('input[name="subject"]').val(data.subject);
                $('input[name="text"]').val(data.text);
                if(data.opened == true){
                    $('input[name="opened"]').prop('checked', true);
                }else{
                    $('input[name="opened"]').prop('checked', false);
                }
                $('input[name="send"]').val(convertDateTime(data.created));
                $('input[name="notif_id"]').val(data.id);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się pobrać powiadomienia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

$(".edit_modal_notif").click(function(){
    let id = $('input[name="notif_id"]').val();
    let data = $('#edit_notification_form').serialize();
    var value = $('input[type="checkbox"]').prop('checked');
    data += '&opened=' + value;
    editNotification(id, data);
});

function editNotification(id, data){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/notifications/' + id,
            type: 'PUT',
            dataType: 'json',
            data: data,
            async: true,
            jsonp: false,

            success: function(data){
                $('.body_notifications_list').html('');
                let to_user = $("#to_user").val();
                getNotifications(to_user);

                $('.admin-modal').css('display', 'none');
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się edytować powiadomienia',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

// ACTIONS MESSAGES API

$(document).on("click",".messages_clear", function () {
    let from_user = $(this).data('fid');
    let to_user = $(this).data('sid');
    Swal.fire({
        icon: 'warning',
        title: 'Usuwanie wiadomości',
        text: 'Czy na pewno chesz usunąć wiadomości?',
        showCancelButton: true,
        confirmButtonText: 'Usuń',
        cancelButtonText: 'Anuluj',
    }).then((result) => {
        if(result.isConfirmed){
            clearMessages(from_user, to_user);
        }
    });
});

function clearMessages(from, to){
    $(document).ready(function(){
        $.ajax({
            url: '/api/v1/admin/messages',
            type: 'DELETE',
            dataType: 'json',
            data: {
                from: from,
                to: to
            },
            async: true,
            jsonp: false,

            success: function(data){
                $('.body_messages_list').html('');
                let from_user = $("#from_user").val();
                let to_user = $("#to_user").val();
                getMessages(from_user, to_user);
            },

            error : function(xhr, textStatus, errorThrown) {  
                Swal.fire({
                    icon: 'error',
                    title: 'Błąd',
                    text: 'Nie udało się wyczyścić wiadomości',
                    footer: 'Błąd: ' + errorThrown 
                })
            }  
        });
    });
}

// LIMIT RECORDS 

$(document).on("click",".r_20", function () {
    limit = 20;
    if(getCurrentPathName() == '/admin/users'){
        query = $("input[name='query']").val();
        role =  $("select[name='role']").val();
        getUsers();
    }
    if(getCurrentPathName() == '/admin/invitations'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getInvitations(from_user, to_user);
    }
    if(getCurrentPathName() == '/admin/friends'){
        let user = $('select[name="user_search"]').val();
        let friend =  $('select[name="friend_search"]').val();
        getFriends(user, friend);
    }
    if(getCurrentPathName() == '/admin/notifications'){
        let to_user = $("#to_user").val();
        getNotifications(to_user);
    }
    if(getCurrentPathName() == '/admin/messages'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getMessages(from_user, to_user);
    }
});
$(document).on("click",".r_50", function () {
    limit = 50;
    if(getCurrentPathName() == '/admin/users'){
        query = $("input[name='query']").val();
        role =  $("select[name='role']").val();
        getUsers();
    }
    if(getCurrentPathName() == '/admin/invitations'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getInvitations(from_user, to_user);
    }
    if(getCurrentPathName() == '/admin/friends'){
        let user = $('select[name="user_search"]').val();
        let friend =  $('select[name="friend_search"]').val();
        getFriends(user, friend);
    }
    if(getCurrentPathName() == '/admin/notifications'){
        let to_user = $("#to_user").val();
        getNotifications(to_user);
    }
    if(getCurrentPathName() == '/admin/messages'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getMessages(from_user, to_user);
    }
});
$(document).on("click",".r_100", function () {
    limit = 100;
    if(getCurrentPathName() == '/admin/users'){
        query = $("input[name='query']").val();
        role =  $("select[name='role']").val();
        getUsers();
    }
    if(getCurrentPathName() == '/admin/invitations'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getInvitations(from_user, to_user);
    }
    if(getCurrentPathName() == '/admin/friends'){
        let user = $('select[name="user_search"]').val();
        let friend =  $('select[name="friend_search"]').val();
        getFriends(user, friend);
    }
    if(getCurrentPathName() == '/admin/notifications'){
        let to_user = $("#to_user").val();
        getNotifications(to_user);
    }
    if(getCurrentPathName() == '/admin/messages'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getMessages(from_user, to_user);
    }
});
$(document).on("click",".r_all", function () {
    limit = 'all';
    if(getCurrentPathName() == '/admin/users'){
        query = $("input[name='query']").val();
        role =  $("select[name='role']").val();
        getUsers();
    }
    if(getCurrentPathName() == '/admin/invitations'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getInvitations(from_user, to_user);
    }
    if(getCurrentPathName() == '/admin/friends'){
        let user = $('select[name="user_search"]').val();
        let friend =  $('select[name="friend_search"]').val();
        getFriends(user, friend);
    }
    if(getCurrentPathName() == '/admin/notifications'){
        let to_user = $("#to_user").val();
        getNotifications(to_user);
    }
    if(getCurrentPathName() == '/admin/messages'){
        let from_user = $("#from_user").val();
        let to_user = $("#to_user").val();
        getMessages(from_user, to_user);
    }
});

// CLOSE MODAL ADMIN INFO
$(".close_modal_info").click(function(){
    $(document).ready(function (){
        $('.admin-modal').css('display', 'none');
    });
});
function convertDateTime(date){
    var tzoffset = (new Date()).getTimezoneOffset() * 60000;
    
    date = (new Date(Date.parse(date) - tzoffset)).toISOString().slice(0, -1).replace('T', ' ');
    date = date.slice(0, -7)

    let time = date.split(" ");

    time[0] = convertDate(time[0]);

    return time[0] + ' ' + time[1];
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
function isEmpty(obj) {
    return Object.keys(obj).length === 0;
}

function getCurrentPathName () {
    return window.location.pathname;
}
