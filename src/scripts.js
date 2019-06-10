/**
 * Funktion der henter data for filter, pakker det ind i html, indsætter på siden og tilføjer relevante klik-funktioner.
 * Funktionen kaldes, når der sker opdateringer af tags på siden, hvor filteret skal tilpasse.
 */
var getFilter = function(){
  $.get("api/tags/read.php", 'json')
  .done(function(response) {
    
    if (response.error) {
      alert("Du er ikke autoriseret til at hente data.")
    } else {
      
      // Tags sorteres alfabetisk
      response.sort(function(a, b) {
          if ( a.tagName < b.tagName )
              return -1;
          if ( a.tagName > b.tagName )
              return 1;
          return 0;
      });
      
      // Konstruer html for tag-knapper    
      var html = '';
    
      html += '<form id="knapper">';
      html += '  <div class="custom-control custom-radio custom-control-inline">';
      html += '    <input type="radio" class="custom-control-input" id="alle" name="example" value="customEx" checked="checked" />';
      html += '    <label class="custom-control-label" for="alle">Vis alle</label>';
      html += '  </div>';
      
      $.each(response, function(index, tag) {
        
        html += '  <div class="custom-control custom-radio custom-control-inline">';
        html += '    <input type="radio" class="custom-control-input tag-knap" id="tag-' + tag.tagId + '" name="example" value="customEx" />';
        html += '    <label class="custom-control-label" for="tag-' + tag.tagId + '">' + tag.tagName + '</label>';
        html += '  </div>';
      });
      
      html += '</form>';
      
      // Insæt html i filter-pladsholder   
      $('#filter').html(html);
      
      // Sæt klik-funktioner på filter  
      $(".tag-knap").change(function() {
        // Hvis knappen er blevet markeret
        if(this.checked) {
          // find det valgte tag
          var tag = $(this).attr('id');
          
          // Løb alle albums igennem
          $('article').each(function() {
             
            // Findes tagget på albummet+ 
            var count = $(this).find($('.' + tag)).length;
            
            if (count > 0) {
              $(this).show()
            } else {
              $(this).hide()
            }
          }); 
        }
      });
    
      // Sæt klik-funktion på Vælg alle-knappen
      $('#alle').change(function() {
        // Hvis knappen er blevet markeret
        if(this.checked) {    
          // Løb alle albums igennem
          $('article').each(function() {
              $(this).show()    
          });                                                                       
        }
      });
      
    }
    
  });
};


/* Funktion der henter bruger */
var getUser = function () {
  
  $.get('api/users/read.php', 'json')
  .done(function(response){

    $('#user').find('#profile').attr("src", response.userImgUrl); 
    $('#name').html(response.userDisplayName);
    
    $('#user').show();  
  });
  
  // Popover til logud
  $(function () {
    $('#logout-link').popover({
      container: 'body',
      trigger: 'focus',
      html: true,
      template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
      
      content: '<div id="logout-content"><a href="controllers/logout.php">Log ud</a></div>'
    });
  });

  $('#logout-link').on('show.bs.popover', function () {
    $('#down').hide();
    $('#up').show();
  })
  
  $('#logout-link').on('hide.bs.popover', function () {
    $('#down').show();
    $('#up').hide();
  })
   
}

// funktion der sletter tag i database
var deleteFunction = function(){
    
    var tagId = $(this).find('.text').attr('class').slice(9);
    var userAlbumId = $(this).parents('.album').attr('id').slice(3);
    
    var node = $(this);

    if(confirm("Vil du slette dette tag?")){
      
      $.get("api/tags/delete.php", {tagId : tagId, userAlbumId : userAlbumId}, function(response){
        
        if (response.error){
          alert("Der skete en fejl!");
        } else {
          node.remove();   
        }
      }, 'json')
      .done(getFilter);
    }
    return false;
};


 
// Aktiver autocomplete-funktion i modal
$(document).ready(function(){
  
  // Tilføj en autocomplete-funktion til input-feltet 
  $( ".input-field").autocomplete({
    source: function(request, response) {
      
      var suggestions = [];
      
      $.get("api/tags/read.php", 'json')
      .done(function(data){
      
        $.each(data, function(key, tag) {
          
          if (tag.tagName.startsWith(request.term)) {
           suggestions.push(tag.tagName);
          }   
        });
        response(suggestions);        
      });
      
    }
  });
  
  
  // Tilføj et ajax-kald når der trykkes tilføj tag  
  $(".oprettag").submit(function( event ) {
    
    // Fjern default-handling fra formular
    event.preventDefault();
    
    // Hent variable
    var $form = $(this);
    name = $form.find( "input[name='name']" ).val();
    useralbumid = $form.find("input[name='useralbumid']").val();
 
    // Send forespørgsel
   $.post( 'api/tags/create.php', { name: name, useralbumid: useralbumid }, function(response){
      
      if (response.error == "Empty tag") {
        alert("Tagget må ikke være tomt!");
      } else if (response.error == "Tag duplicate") {
        alert("Tagget findes i forvejen!");
        $('#addTagModal').find("input[name='name']").val("");
      } else {
        
        var tag = response;
        
        html = '<a href="#" class="delete"><span class="badge badge-pill badge-primary tag"><span class="text tag-' + tag.tagId + '">' + tag.tagName + '</span> <i class="fas fa-times-circle"></i></span></a>';
       
        $('#ua-' + useralbumid).find(".tagpadder").append(html);
        $('#ua-' + useralbumid).find(".tagpadder").children().last().click(deleteFunction);
        
        $('#addTagModal').modal('hide');
        $('#addTagModal').find("input[name='name']").val("");
      }  
    }, 'json')
    // Og filtret genindlæses
    .done(getFilter);
    return false;
  });
});


// Tilføj album-info til modal
$('#addTagModal').on('show.bs.modal', function (event) {
  
  // Knappen der aktiverede modal
  var button = $(event.relatedTarget);
  
  // Hent data vedr. album fra data-attribut
  var useralbumid = button.data('useralbumid'); 
  var url = button.data('url');
  
  // Opdater elementer i modal
  $(this).find('#albumImg').attr("src", url);
  $(this).find('.modal-body input[name="useralbumid"]').val(useralbumid);
});



/* Funktion der henter alt data vedrørende albums og tags på serveren.
 * Hvis serveren angiver at brugeren ikke er autoriseret eller at biblioteket
 * ikke er synkroniseret, håndteres dette.
 */ 

$(document).ready(function(){
  
  $.get("api/albums/read.php", function(response) {
    
    if (response.error == "Not Authorized") {
      // Hvis bruger ikke er autoriseret, laves en modal med en login-knap til autorisering hos Spotify.
      $('#loginModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    } else if (response.error == "Not Syncronized") {
      // Hvis brugers bibliotek ikke er synkroniseret, laves modal, der viser at biblioteket synkroniseres,
      // synkroniseringsfunktionaliteten på serveren kaldes, og siden genindlæses, når biblioteket er klar.
      $('#syncModal').modal({
            backdrop: 'static',
            keyboard: false          
        });
        $.get("controllers/syncronize.php", function(response){
          
          if (response.error == "Not Authorized") {
            alert("Du er ikke logget ind!");
          } else {
            window.location = 'index.html';
          }
        });
    } else {
      
      // Hvis bruger er både autoriseret og synkroniseret, hentes og indpakkes data og puttes på siden,
      // slet-knapperne på tags aktivereres og filteret indlæses.
      
      var html = '';
      
      $.each(response, function(index, userAlbum) {
       

       html += '  <article class="col-xs-12 col-sm-6 col-md-4 col-lg-3 album" id="ua-' + userAlbum.userAlbumId + '">';
       html += '    <div class="padder">';
       html += '        <div class="imagepadder">';
       html += '        <a class="play" href="https://open.spotify.com/album/' + userAlbum.album.albumId + '"><i class="far fa-play-circle"></i></a>'
       html += '        <div class="editwrapper">';
       html += '          <a href="#" class="edit" data-placement="left" data-toggle="modal" data-target="#addTagModal" data-url="' + userAlbum.album.albumImgUrl + '" data-useralbumid="' + userAlbum.userAlbumId +'"><i class="fas fa-plus"></i></a>';
       html += '        </div>';
       html += '      <div class="tagpadder">';

       $.each(userAlbum.tagList, function(index, tag) {                           
        html += '       <a href="#" class="delete"><span class="badge badge-pill badge-primary tag"><span class="text tag-' + tag.tagId + '">' + tag.tagName + '</span> <i class="fas fa-times-circle"></i></span></a>';
        }); 
        html += '      </div>';
        html += '      <img src="' + userAlbum.album.albumImgUrl + '" alt="Responsive image">';
        html += '    </div>';
        html += '    <p class="title">' + userAlbum.album.albumTitle + '</p>';
        html += '    <p class="artist">' + userAlbum.album.albumArtist + '</p>';
        html += '    <div class="clearfix"></div>';
        html += '  </div>';
        html += '  </article>';

      });      
      
      // html indsættes i pladsholderen
      $('#albums').html(html);
      
      // der sættes funktionalitet på slet-knapperne på alle tags.
      $(".delete").click(deleteFunction);

      // Der sættes hover-effekt på albums
      $(".imagepadder")
      .mouseenter(function(){
        $(this).find('.play').fadeIn(200);
      })
      .mouseleave(function(){
        $(this).find('.play').fadeOut(200); 
      });
      
      
      // Filter indlæses
      getFilter();
      
      // Hent bruger
      getUser();
    }  
  }, 'json'); 
});


