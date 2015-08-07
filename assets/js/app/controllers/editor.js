/**
 * Controller for the main editor
 */
app.controller('EditorCtrl', function($scope, $animate, data, content, templates, layouts, history) {
    angular.extend($scope, content, templates, layouts, history);
    $scope.data = data.contentStructure;


    $scope.$watch(function() {
        return history.data;
    }, function(value) {
        $scope.history = value;
    });


    // data received from php
    $scope.allowedRowLayouts = layotterData.allowedRowLayouts;
    $scope.optionsEnabled = {
        post: layotterData.options.post.enabled,
        row: layotterData.options.row.enabled,
        col: layotterData.options.col.enabled,
        element: layotterData.options.element.enabled
    };
    $scope.enablePostLayouts = layotterData.enablePostLayouts;
    $scope.enableElementTemplates = layotterData.enableElementTemplates;
    $scope.savedLayouts = layouts.savedLayouts;


    // on content change, update textarea
    $scope.$watch('data', function(value) {
        // remove views from JSON data, no need to store them as they're always regenerated before output
        var valueClone = angular.copy(value);
        var searchDump = '';
        angular.forEach(valueClone.rows, function(row){
            angular.forEach(row.cols, function(col){
                angular.forEach(col.elements, function(element){
                    searchDump += element.searchdump;
                    delete element.view;
                    delete element.searchdump;
                });
            });
        });

        // enter JSON data as string wrapped in layotter shortcode into textarea
        var jsonAsString = angular.toJson(valueClone, false); // change to true for pretty JSON
        jQuery('#content').val('[layotter]' + searchDump + '[/layotter]');
        jQuery('#acf-field_layotter_post_content').val(jsonAsString);
    }, true);


    // options for jQuery UI's sortable (via ui.sortable)
    $scope.rowSortableOptions = {
        items: '.layotter-row',
        placeholder: 'layotter-placeholder',
        forcePlaceholderSize: true,
        revert: 300,
        handle: '.layotter-row-move',
        stop: function(){
            history.pushStep(layotterData.i18n.history.move_row);
        }
    };
    $scope.elementSortableOptions = {
        items: '.layotter-element',
        cancel: '.layotter-element-buttons',
        placeholder: 'layotter-placeholder',
        forcePlaceholderSize: true,
        revert: 300,
        connectWith: '#layotter .layotter-elements',
        // prevent slide-in animation after moving an element
        start: function(){
            $animate.enabled(false);
        },
        stop: function(){
            $animate.enabled(true);
            history.pushStep(layotterData.i18n.history.move_element);
        }
    };
});