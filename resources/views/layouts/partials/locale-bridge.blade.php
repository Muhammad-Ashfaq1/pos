<script>
    window.AppLocale = @json(app()->getLocale());
    window.AppDirection = @json(app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
    window.AppTranslations = Object.assign({}, window.AppTranslations || {}, {
        delete: @json(__('app.delete')),
        cancel: @json(__('app.cancel')),
        confirm: @json(__('app.confirm')),
        save: @json(__('app.save')),
        saving: @json(__('app.saving')),
        loading: @json(__('app.loading')),
        noData: @json(__('app.no_data')),
        edit: @json(__('app.edit')),
        close: @json(__('app.close')),
        search: @json(__('app.search')),
        yesDeleteIt: @json(__('app.yes_delete_it')),
        unableToSave: @json(__('app.unable_to_save')),
        unableToDelete: @json(__('app.unable_to_delete')),
        unableToLoad: @json(__('app.unable_to_load')),
        admin: @json(__('admin')),
    });
    window.appTranslate = window.appTranslate || function (key, fallback, replacements) {
        const readPath = function (source, path) {
            return String(path).split('.').reduce(function (carry, segment) {
                return carry && Object.prototype.hasOwnProperty.call(carry, segment) ? carry[segment] : undefined;
            }, source);
        };

        let value = readPath(window.AppTranslations || {}, key);

        if (value === undefined && window.AppTranslations) {
            value = window.AppTranslations[key];
        }

        if (typeof value !== 'string') {
            value = fallback || key;
        }

        Object.entries(replacements || {}).forEach(function (entry) {
            value = String(value).replaceAll(':' + entry[0], entry[1]);
        });

        return value;
    };
</script>
