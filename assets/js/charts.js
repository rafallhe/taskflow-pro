(() => {
 const cfg=window.taskflowCharts;
 if(!cfg || typeof Chart==='undefined') return;
 const grid='rgba(148,163,184,.12)';
 const text='#94a3b8';

 const commonScales={
  x:{ticks:{color:text},grid:{color:grid}},
  y:{beginAtZero:true,ticks:{color:text,precision:0},grid:{color:grid}}
 };

 const weekly=document.getElementById('weeklyChart');
 if(weekly)new Chart(weekly,{
  type:'line',
  data:{labels:cfg.weekly.labels,datasets:[{label:'Tasks',data:cfg.weekly.data,tension:.35,fill:true}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:commonScales}
 });

 const monthly=document.getElementById('monthlyChart');
 if(monthly)new Chart(monthly,{
  type:'bar',
  data:{labels:cfg.monthly.labels,datasets:[{label:'Created',data:cfg.monthly.data,borderRadius:8}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:commonScales}
 });

 const priority=document.getElementById('priorityChart');
 if(priority)new Chart(priority,{
  type:'doughnut',
  data:{labels:cfg.priority.labels,datasets:[{data:cfg.priority.data}]},
  options:{responsive:true,cutout:'68%',plugins:{legend:{position:'bottom',labels:{color:text,padding:18}}}}
 });
})();
