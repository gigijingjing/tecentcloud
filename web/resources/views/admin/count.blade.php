<form action="{{url('/admin?_pjax=%23pjax-container')}}" class="form-horizontal" pjax-container method="get">

    <div class="row">
        <div class="col-md-12">
            <div class="box-body">
                <div class="fields-group">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">统计时间范围</label>
                        <div class="col-sm-8" style="width: 390px">
                            <div class="input-group input-group-sm">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control" id="time_start" placeholder="开始时间" name="time_start" value="{{$timeStart}}">
                                <span class="input-group-addon" style="border-left: 0; border-right: 0;">-</span>
                                <input type="text" class="form-control" id="time_end" placeholder="结束时间" name="time_end" value="{{$timeEnd}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="btn-group pull-left">
                        <button class="btn btn-info submit btn-sm">
                            <i class="fa fa-search"></i>&nbsp;&nbsp;统计
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<script>
    $(function () {
        $('#time_start').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"zh-CN"});
        $('#time_end').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"zh-CN","useCurrent":false});
        $("#time_start").on("dp.change", function (e) {
            $('#time_end').data("DateTimePicker").minDate(e.date);
        });
        $("#time_end").on("dp.change", function (e) {
            $('#time_start').data("DateTimePicker").maxDate(e.date);
        });
    });
</script>
