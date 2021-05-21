<script>
    $($('input[name="mode"]').parent()).click(function() {
        let val = $($($(this).children()[0]).children()[0]).val();
        showInput(val);
    });
    function  showInput(val) {
        let registerNum = $("#register_num");
        let registerPrice = $("#register_price");
        let cpa = $("#cpa");
        let cps = $("#cps");
        let loanMoney = $("#loan_money");
        let loanNum = $("#loan_num");
        let cpaParent = cpa.parent().parent().parent();
        let cpsParent = cps.parent().parent().parent();
        let loanMoneyParent = loanMoney.parent().parent().parent();
        let loanNumParent =loanNum.parent().parent().parent();
        switch (val) {
            case 'cpa':
                cpaParent.show();
                cpsParent.hide();
                loanMoneyParent.hide();
                loanNumParent.hide();
                break;
            case 'cps':
                cpsParent.show();
                cpaParent.hide();
                loanMoneyParent.show();
                loanNumParent.show();
                break;
            case 'cpa+cps':
                cpsParent.show();
                cpaParent.show();
                loanMoneyParent.show();
                loanNumParent.show();
                break;
        }
        cpa.on('input', compute);
        cps.on('input', compute);
        loanMoney.on('input', compute);
        loanNum.on('input', compute);
        registerNum.on('input', compute);
        registerPrice.on('input', compute);
        // 计算
        function compute() {
            let conversion = $("#conversion");
            let income = $("#income");
            let number = 0;
            switch (val) {
                case 'cpa':
                    // 转化率
                    number = Math.floor((parseInt(registerNum.val()) / parseInt('{{isset($clickNum) ? $clickNum : 0}}')).toFixed(2) * 100);
                    if (number) {
                        conversion.val(number);
                    }
                    // 推广收益
                    number = Math.floor(parseInt(registerNum.val()) * parseInt(registerPrice.val()));
                    if (number) {
                        income.val(number);
                    }
                    break;
                case 'cps':
                    // 转化率
                    number = Math.floor((parseInt(loanNum.val()) / parseInt(registerNum.val())).toFixed(2) * 100)
                    if (number) {
                        conversion.val(number);
                    }
                    // 推广收益
                    number = Math.floor(parseFloat(cps.val()) * parseInt(loanMoney.val()))
                    if (number) {
                        income.val(number);
                    }
                    break;
                case 'cpa+cps':
                    // 转化率
                    let cpaConversion = Math.floor((parseInt(registerNum.val()) / parseInt('isset($clickNum) ? $clickNum : 0')).toFixed(2) * 100);
                    let cpsConversion = Math.floor((parseInt(loanNum.val()) / parseInt(registerNum.val())).toFixed(2) * 100);
                    number = (cpsConversion + cpaConversion) / 2;
                    if (number) {
                        conversion.val(number);
                    }
                    // 推广收益
                    let cpaIncome = Math.floor(parseInt(registerNum.val()) * parseInt(registerPrice.val()));
                    let cpsIncome = Math.floor(parseFloat(cps.val()) * parseInt(loanMoney.val()));
                    number = cpaIncome + cpsIncome;
                    if (number) {
                        income.val(number);
                    }
                    break;
            }
        }
        compute();
    }
    showInput('{{$mode}}');
</script>
