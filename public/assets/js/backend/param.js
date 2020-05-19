define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'param/index' + location.search,
                    add_url: 'param/add',
                    edit_url: 'param/edit',
                    del_url: 'param/del',
                    multi_url: 'param/multi',
                    table: 'param',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:true,
                searchFormVisible: true,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'project_id', title: __('Project_id'), searchList: projectArr, formatter:function(value, row, index){
                            return projectArr[value];
                        }},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'param', title: __('Param'), operate: 'LIKE %...%', placeholder: '关键字，模糊搜索'},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'mtime', title: __('Mtime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});