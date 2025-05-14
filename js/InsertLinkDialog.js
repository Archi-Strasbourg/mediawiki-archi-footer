/*jslint browser: true*/
/*global OO, $*/

function InsertLinkDialog(config) {
  InsertLinkDialog.super.call(this, config);
}
OO.inheritClass(InsertLinkDialog, OO.ui.ProcessDialog);

InsertLinkDialog.static.name = 'afinsertlink';
InsertLinkDialog.static.title = 'Insérer un lien';
InsertLinkDialog.static.actions = [
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

InsertLinkDialog.prototype.initialize = function () {
  InsertLinkDialog.super.prototype.initialize.apply(this, arguments);

  this.content = new OO.ui.PanelLayout({padded: true, expanded: false});

  this.content.$element.append(
    `<div class="af-insertlink__wrapper">
       <label for="af-insertlink">URL</label>
       <input type="url" id="af-insertlink" placeholder="https://...">
       <label for="af-insertlink-label">Libellé (optionnel)</label>
       <input type="text" id="af-insertlink-label">
    </div>`
  );
  this.$body.append(this.content.$element);
};

InsertLinkDialog.prototype.getBodyHeight = function () {
  return this.content.$element.outerHeight( true );
};

InsertLinkDialog.prototype.getSetupProcess = function (data) {
  return InsertLinkDialog.super.prototype.getSetupProcess.call(this, data).next(function () {

  }, this);
};

InsertLinkDialog.prototype.getActionProcess = function (action) {
  if (action === 'save') {
    return new OO.ui.Process(() => {
      const urlInput = document.getElementById('af-insertlink');
      const labelInput = document.getElementById('af-insertlink-label');

      if (urlInput.value.length) {
        const commentTextarea = document.querySelector('textarea#comment');
        if (commentTextarea) {
          if (labelInput.value.length) {
            commentTextarea.value += `[${urlInput.value} ${labelInput.value}] `;
          } else {
            commentTextarea.value += `[${urlInput.value}] `;
          }
        }
      }
      urlInput.value = '';
      labelInput.value = '';

      this.close({action: action});
    });
  } else if (action === 'cancel') {
    return new OO.ui.Process(() => {
      const urlInput = document.getElementById('af-insertlink');
      const labelInput = document.getElementById('af-insertlink-label');
      urlInput.value = '';
      labelInput.value = '';

      this.close({action: action});
    });
  }
  return InsertLinkDialog.super.prototype.getActionProcess.call( this, action );
};

OO.ui.getWindowManager().addWindows( [ new InsertLinkDialog() ] );
