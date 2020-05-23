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
                        {field: 'total_daily_num', title: __('输出量')},
                        {field: 'channel_total_daily_num', title: __('回调量')},
                        {field: 'charge_type', title: __('计费类型'),formatter:function (value, row, index) {
                                if(value=="1"){
                                    return '短信';
                                }
                                return value;
                            }
                        },

                        {field: 'sms', title: __('sms')},
                        {field: 'remarks', title: __('备注')},
                        {field: 'mtime', title: __('修改时间'), formatter: Table.api.formatter.datetime},
                        {field: 'isstart', title: '开关',formatter:Table.api.formatter.toggle},
                        {field: 'copy', title: '复制',formatter:function (value, row, index) {
                                return '<a href="javascript:void(0);" class="btn btn-xs copy" data-id="' + row['id'] + '"><i class="fa fa-copy"></i></a>';
                            }
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            var _this = this;
            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                $(".copy").click(function(){
                    var _this = this;
                    Layer.confirm(__('确认复制?'), function () {
                        var id = $(_this).attr("data-id");
                        $.ajax({
                            url: "/admin/task/copy", data: {id: id}, success: function (data) {
                                if (data.code == 200) {
                                    Layer.closeAll();
                                    table.bootstrapTable('refresh');
                                }
                            }
                        });
                    });
                });
            });

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
        },
    };
    return Controller;
});