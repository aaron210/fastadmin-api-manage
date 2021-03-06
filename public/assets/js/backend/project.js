define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-datetimepicker'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project/index' + location.search,
                    add_url: 'project/add',
                    edit_url: 'project/edit',
                    del_url: 'project/del',
                    multi_url: 'project/multi',
                    table: 'project',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'ename', title: __('外部访问地址'),formatter:function (value, row, index) {
                                var host = "http://"+window.location.host+"/";
                                return host+value;
                            }
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            this.preview();
        },
        edit: function () {
            Controller.api.bindevent();
            this.preview();

                // $('#c-caozuotime').datetimepicker({
                //     format: 'LT'
                // });

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },

        // 预览加载
        preview: function(){
            $(".makePreview").click(function(){
                var data = $('.form-horizontal').serialize();
                $.ajax({url:"/admin/project/makePreview",data:data,async:false,success:function(data){
                    $(".preview").val(data);
                }});
            });
        }

    };
    return Controller;
});