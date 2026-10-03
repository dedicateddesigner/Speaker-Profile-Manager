jQuery(function ($) {
    const originalEdit = inlineEditPost.edit;
    inlineEditPost.edit = function (id) {
        originalEdit.apply(this, arguments);
        let postId = 0;
        if (typeof id === 'object') postId = parseInt(this.getId(id), 10);
        if (!postId) return;
        const value = $('#spm-order-' + postId).text().trim() || '1';
        $('#edit-' + postId + ' .spm-quick-order').val(value);
    };
});
