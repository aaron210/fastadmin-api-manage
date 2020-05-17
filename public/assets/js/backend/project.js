define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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
                        {field: 'province', title: __('省份'),formatter:function (value, row, index) {
                                if(value>=0){
                                    return province[value];
                                }
                                return value;
                            }
                        },
                        {field: 'operators', title: __('运营商'),formatter:function (value, row, index) {
                                if(value=="yidong"){
                                    return '移动'
                                }
                                return value;
                            }
                        },
                        {field: 'total_daily', title: __('每日总量')},
                        {field: 'charge_type', title: __('计费类型'),formatter:function (value, row, index) {
                                if(value=="1"){
                                    return '短信'
                                }
                                return value;
                            }
                        },
                        {field: 'isstart', title: __('开关'),formatter:function (value, row, index) {
                                return value==0 ? "关" : "开";
                            }
                        },
                        {field: 'ename', title: __('外部访问地址'),formatter:function (value, row, index) {
                                var host = "http://"+window.location.host+"/";
                                return host+value;
                            }
                        },
                        {field: 'sms', title: __('sms')},
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
                console.log(data);
                $.ajax({url:"/admin/project/makePreview",data:data,async:false,success:function(data){
                    $(".preview").html(data);
                }});
            });
        }

    };
    return Controller;
});