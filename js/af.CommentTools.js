/*jslint browser: true*/
/*global OO*/
(function () {
  'use strict';
  require('./AutoCompleteDialog.js');

  function handleReply(evt) {
    const commentID = evt.currentTarget.getAttribute('data-comment-id');
    const commentElement = document.getElementById(`comment-${commentID}`);
    if (!commentElement) {
      return;
    }
    const commentAuthorName = commentElement.querySelector('.c-user > a').innerText;

    if (commentAuthorName.length) {
      const commentTextarea = document.querySelector('textarea#comment');
      commentTextarea.value += `@[[Utilisateur:${commentAuthorName}|${commentAuthorName}]] `;
    }
    return true;
  }

  function init() {
    const toolFactory = new OO.ui.ToolFactory();
    const toolGroupFactory = new OO.ui.ToolGroupFactory();
    const toolbar = new OO.ui.Toolbar(toolFactory, toolGroupFactory);

    function AutoCompleteTool() {
      AutoCompleteTool.super.apply(this, arguments);
    }
    OO.inheritClass(AutoCompleteTool, OO.ui.Tool);

    AutoCompleteTool.static.name = 'autocomplete';
    AutoCompleteTool.static.icon = 'userGroup';
    AutoCompleteTool.static.title = 'Mentionner';
    AutoCompleteTool.static.autoAddToCatchall = false;

    AutoCompleteTool.prototype.onSelect = function() {
      OO.ui.getWindowManager().openWindow('afautocomplete', {size: 'small'});

      this.setActive(false);
    };
    AutoCompleteTool.prototype.onUpdateState = function () {};

    toolFactory.register(AutoCompleteTool);

    toolbar.setup([
      {
        type: 'bar',
        include: ['autocomplete']
      }
    ]);
    const formElement = document.querySelector('form[name="commentForm"]');
    const textareaElement = formElement.querySelector('textarea#comment');

    toolbar.$element.insertBefore(textareaElement);
    toolbar.initialize();
    toolbar.emit('updateState');

    const replyLinks = document.querySelectorAll('.comments-reply-to');
    replyLinks.forEach(elm => {
      elm.addEventListener('click', handleReply);
    });
  }


  const commentsBody = document.getElementById('comments-body');
  const config = { childList: true };

  const mutationCallback = (mutationList, observer) => {
    let found = false;
    mutationList.forEach(mutation => {
      if (mutation.addedNodes.length) {
        [].forEach.call(mutation.addedNodes, elm => {
          if (elm.nodeName === 'FORM' && elm.getAttribute('name') === 'commentForm') {
            found = true;
          }
        });
      }
    });
    if (found) {
      init();
      observer.disconnect();
    }
  };

  const observer = new MutationObserver(mutationCallback);
  observer.observe(commentsBody, config);
})();



