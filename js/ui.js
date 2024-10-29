(function() {
  var D = document,
    addCustomEventBtn = D.querySelector('button[data-action="add-custom-event"]'),
    webPushCheckbox = D.querySelector('input[name="push_enabled"]');

  /**
   * Inserts a dom element after a reference node
   * @param {{}} newNode
   * @param {{}} referenceNode
   * @returns {{}}
   */
  function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
  }

  /**
   * Creates a dom element, can pass an optional attributes object which will give the top
   * level node any attributes you want such as { className: 'container', data-action: 'submit' }
   * @param {String} tag
   * @param {String} innerHtml
   * @param {{}} attributes
   * @returns {{}}
   */
  function createEl(tag, innerHtml, attributes) {
    var el = document.createElement(tag);

    el.innerHTML = innerHtml;
    if (typeof attributes !== 'undefined') {
      for (var key in attributes) {
        if (attributes.hasOwnProperty(key)) {
          el[key] = attributes[key];
        }
      }
    }

    return el;
  }

  /**
   * Click handler for close button
   */
  function handleCloseButton(button) {
    button.parentNode.parentNode.removeChild(button.parentNode);
  }

  /**
   * Click handler for custom events check box
   */
  function handlecustomEventsAddButton() {
    insertAfter(createEl(
      'div',
      '<input autofocus required name="custom_event_names[]" placeholder="Custom event name" type="text"><input required name="custom_event_selectors[]" placeholder="DOM selector" type="text"><input required name="custom_event_events[]" placeholder="DOM event" type="text"><button type="button" data-action="remove-setting" class="button-secondary">&times;</button>'
    ), this);
  }

  /**
   * Click handler for web push checkbox
   */
  function handleWebPushCheckbox(e) {
    if (e.target.checked) {
      insertAfter(createEl(
        'div',
        '<input required type="text" name="gcm_sender_id" placeholder="Your GCM project #"><div><input type="text" name="safari_web_push_id" placeholder="Safari website push ID"></div><br><br><div><strong><small>If you want a custom UI event to trigger push permission add the selector and DOM event here:</small></strong><br><input name="request_push_permission_selector" type="text" placeholder="DOM selector"><input name="request_push_permission_event" type="text" placeholder="DOM event"></div>',
        { className: 'web-push-config' }
      ), this);
    } else {
      document.querySelector('.web-push-config').remove();
    }
  }

  /**
   * Attaches all handlers
   * @param {{}} doc - document object
   */
  function attachHandlers(doc) {
    addCustomEventBtn.addEventListener('click', handlecustomEventsAddButton, false);
    webPushCheckbox.addEventListener('click', handleWebPushCheckbox, false);
    doc.body.addEventListener('click', function(event) {
      if (event.target.getAttribute('data-action')) {
        if (event.target.getAttribute('data-action').toLowerCase() === 'remove-setting') {
          handleCloseButton(event.target);
        }
      }
    });
  }

  attachHandlers(D);
})();
