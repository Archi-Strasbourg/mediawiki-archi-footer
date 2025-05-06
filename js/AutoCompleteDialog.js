/*jslint browser: true*/
/*global OO, $, mw*/

function AutoCompleteDialog(config) {
  AutoCompleteDialog.super.call(this, config);
}
OO.inheritClass(AutoCompleteDialog, OO.ui.ProcessDialog);

AutoCompleteDialog.static.name = 'afautocomplete';
AutoCompleteDialog.static.title = 'Mentionner';
AutoCompleteDialog.static.actions = [
  {
    action: 'save',
    label: 'Confirmer',
    flags: 'primary'
  },
  {
    action: 'cancel',
    label: 'Annuler',
    flags: 'safe'
  }
];


AutoCompleteDialog.prototype.initialize = function () {
  AutoCompleteDialog.super.prototype.initialize.apply(this, arguments);

  this.content = new OO.ui.PanelLayout({padded: true, expanded: false});

  this.content.$element.append(
    `<div class="af-autocomplete__wrapper">
        <label for="af-autocomplete">Chercher par nom d'utilisateur</label>
        <input id="af-autocomplete" autocomplete="off">
        <input type="hidden" id="af-autocomplete-value">
    </div>`
  );
  this.$body.append(this.content.$element);
  this.initAutocomplete();
};

AutoCompleteDialog.prototype.getBodyHeight = function () {
  return this.content.$element.outerHeight( true );
};

AutoCompleteDialog.prototype.getSetupProcess = function (data) {
  return AutoCompleteDialog.super.prototype.getSetupProcess.call(this, data).next(function () {

  }, this);
};

AutoCompleteDialog.prototype.getActionProcess = function (action) {
  if (action === 'save') {
    return new OO.ui.Process(() => {
      $( '#af-autocomplete' ).autocomplete('clear');
      const valueInput = document.getElementById('af-autocomplete-value');
      const value = valueInput.getAttribute('value');

      if (value.length) {
        const commentTextarea = document.querySelector('textarea#comment');
        if (commentTextarea) {
          commentTextarea.value += `@[[Utilisateur:${value}|${value}]] `;
          commentTextarea.focus();
          valueInput.setAttribute('value', '');
          $( '#af-autocomplete' ).autocomplete('clear');
        }
      }

      this.close({
        action: action
      });
    });
  } else if (action === 'cancel') {
    $( '#af-autocomplete' ).autocomplete('clear');
    this.close();
  }
  return AutoCompleteDialog.super.prototype.getActionProcess.call(this, action);
};

AutoCompleteDialog.prototype.initAutocomplete = function () {

  $( '#af-autocomplete' ).autocomplete({
    deferRequestBy: 300,
    minChars: 1,
    appendTo: '.af-autocomplete__wrapper',
    lookup: function(request, response) {
      // TODO : vider la valeur sélectionnée
      const api = new mw.Api();
      api.get( {
        action: 'query',
        list: 'allusers',
        format: 'json',
        auprefix: request,
        auprop: 'blockinfo',
        auwitheditsonly: 1,
        aulimit: 20
      }).done(function (data) {
        response({
          suggestions: data.query.allusers.map(row => {
            return {
              value: row.name,
              data: row.name
            };
          })
        });
      });
    },
    onSelect: function (suggestion) {
      document.getElementById('af-autocomplete-value').value = suggestion.value;
    }
  });
  $( '#af-autocomplete' ).autocomplete('enable');
};

mw.loader.using('mediawiki.api', onApiLoaded);

function onApiLoaded() {
  OO.ui.getWindowManager().addWindows( [ new AutoCompleteDialog() ] );
}
