define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-datetimepicker', 'template'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'task/index' + location.search,
                    add_url: 'task/add',
                    edit_url: 'task/edit',
                    del_url: 'task/del',
                    multi_url: 'task/multi',
                    table: 'task',
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
                        {field: 'project_id', title: __('Project_id'), searchList: projectArr, formatter:function(value, row, index){
                                return projectArr[value];
                            }},
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
                        {field: 'start_time', title: __('执行时间'),formatter:function (value, row, index) {
                                return value + "-" + row['end_time'];
                            }
                        },
                        {field: 'total_daily', title: __('每日总量')},
                        {field: 'total_daily_num', title: __('现已执行量')},
                        {field: 'charge_type', title: __('计费类型'),formatter:function (value, row, index) {
                                if(value=="1"){
                                    return '短信'
                                }
                                return value;
                            }
                        },
                        {field: 'isstart', title: '开关',formatter:Table.api.formatter.toggle},
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
                $.ajax({url:"/admin/task/makePreview",data:data,async:false,success:function(data){
                        $(".preview").html(data);
                    }});
            });
        }
    };
    return Controller;
});