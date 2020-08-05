define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-datetimepicker', 'template'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                clickToSelect: false, //是否启用点击选中
                dblClickToEdit: false, //是否启用双击编辑
                singleSelect: false, //是否启用单选
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
                pageSize: 50,
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
                        // {field: 'operators', title: __('运营商'),formatter:function (value, row, index) {
                        //         if(value=="yidong"){
                        //             return '移动'
                        //         }
                        //         return value;
                        //     }
                        // },
                        {field: 'start_time', title: __('执行时间'),formatter:function (value, row, index) {
                                return value + "-" + row['end_time'];
                            }
                        },
                        {field: 'total_daily', title: __('每日总量'),sortable:true},
                        {field: 'total_daily_num', title: __('输出量')},
                        {field: 'channel_total_daily_num', title: __('回调量')},
                        {field: 'ratio', title: __('输出比例'),formatter:function (value, row, index) {
                                return value + "%";
                            }
                        },
                        {field: 'weight', title: __('权重'),sortable:true},
                        // {field: 'charge_type', title: __('计费类型'),formatter:function (value, row, index) {
                        //         if(value=="1"){
                        //             return '短信';
                        //         }
                        //         return value;
                        //     }
                        // },

                        {field: 'sms', title: __('sms'),formatter:function (value, row, index) {
                                return "<a title='" + value + "'>查看</a>";
                            }
                        },
                        {field: 'remarks', title: __('备注')},
                        {field: 'mtime', title: __('修改时间'), formatter:function (value, row, index) {
                                var datetimeFormat = typeof this.datetimeFormat === 'undefined' ? 'YYYY-MM-DD HH:mm:ss' : this.datetimeFormat;
                                if (isNaN(value)) {
                                    value = value ? Moment(value).format(datetimeFormat) : __('None');
                                } else {
                                    value = value ? Moment(parseInt(value) * 1000).format(datetimeFormat) : __('None');
                                }

                                var nowTimestamp = new Date(new Date().toLocaleDateString()).getTime();
                                var tagTimestamp = (new Date(value)).getTime();

                                console.log(nowTimestamp);
                                console.log(tagTimestamp);

                                if (tagTimestamp > nowTimestamp) {
                                    value = "<span style='color: red;'>" + value + "</span>";
                                }

                                return value;
                            }
                        },
                        {field: 'isstart', title: '开关',formatter:Table.api.formatter.toggle,sortable:true},
                        {field: 'issend', title: '是否推送',formatter:Table.api.formatter.toggle,sortable:true},
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
        statistics: function(){
            // 初始化表格参数配置
            Table.api.init({
                clickToSelect: false, //是否启用点击选中
                dblClickToEdit: false, //是否启用双击编辑
                singleSelect: false, //是否启用单选
                extend: {
                    index_url: 'task/statistics' + location.search,
                },
                queryParams: function (params) {
                    var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                    var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件

                    var filter_date = filter.date;
                    var op_date = op.date;
                    delete filter.date;
                    delete op.date;

                    params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                    params.op = JSON.stringify(op);
                    params.date = filter_date;
                    return params;
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pageSize: 50,
                search:true,
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'project_id', title: __('Project_id'), searchList: projectArr, formatter:function(value, row, index){
                                return projectArr[value];
                            }},
                        {field: 'name', title: __('Name'), operate: false},
                        {field: 'province', title: __('省份'),formatter:function (value, row, index) {
                                if(value>=0){
                                    return province[value];
                                }
                                return value;
                            }
                        },
                        {field: 'total_daily_num', title: __('输出量'), operate: false},
                        {field: 'channel_total_daily_num', title: __('回调量'), operate: false},
                        {field: 'date', title: __('日期'), operate: 'RANGE', addclass: 'datetimepicker',extend:"data-date-format='YYYYMMDD' data-date-max-date='"+date+"'", formatter: Table.api.formatter.datetime, visible: false},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        province_statistics: function(){

            // 初始化表格参数配置
            Table.api.init({
                clickToSelect: false, //是否启用点击选中
                dblClickToEdit: false, //是否启用双击编辑
                singleSelect: false, //是否启用单选
                extend: {
                    index_url: 'task/province_statistics' + location.search,
                },
                queryParams: function (params) {
                    var filter = params.filter ? JSON.parse(params.filter) : {}; //判断当前是否还有其他高级搜索栏的条件
                    var op = params.op ? JSON.parse(params.op) : {};  //并将搜索过滤器 转为对象方便我们追加条件

                    var filter_date = filter.date;
                    var op_date = op.date;
                    delete filter.date;
                    delete op.date;

                    params.filter = JSON.stringify(filter); //将搜索过滤器和操作方法 都转为JSON字符串
                    params.op = JSON.stringify(op);
                    params.date = filter_date;
                    return params;
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pageSize: 50,
                search:true,
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: __('省份'), operate: false},
                        {field: 'total', title: __('数量'), operate: false},
                        {field: 'date', title: __('日期'), operate: 'RANGE', addclass: 'datetimepicker',extend:"data-date-format='YYYY-MM-DD'", formatter: Table.api.formatter.datetime, visible: false},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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