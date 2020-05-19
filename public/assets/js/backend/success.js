define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'success/index' + location.search,
                    add_url: 'success/add',
                    edit_url: 'success/edit',
                    del_url: 'success/del',
                    multi_url: 'success/multi',
                    table: 'success',
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
                        {field: 'uid', title: __('Uid'), operate: false},
                        {field: 'phone', title: __('Phone'), operate: false},
                        {field: 'flag2', title: __('Flag2'), operate: false},
                        {field: 'channel', title: __('Channel'), operate: false},
                        {field: 'version', title: __('Version'), operate: false},
                        {field: 'sms', title: __('Sms'), operate: false},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange'},
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