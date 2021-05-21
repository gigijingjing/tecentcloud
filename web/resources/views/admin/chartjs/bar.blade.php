<canvas id="myChart" width="800" height="200"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myChart").getContext('2d');

        var backgroundColors = [];
        var borderColors = [];
        var labels = [];
        var data = [];
        @foreach($labels as $key => $label)
        var color = Math.floor(Math.random() * 255) + ',' + Math.floor(Math.random() * 255) + ', ' + Math.floor(Math.random() * 255);
        backgroundColors.push('rgba(' + color + ', 0.2)');
        borderColors.push('rgba(' + color + ', 1)');
        labels.push('{{$label}}');
        data.push('{{$data[$key]}}');
        @endforeach

        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '推广统计',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    });
</script>
