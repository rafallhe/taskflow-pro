(() => {
 const board=document.querySelector('.kanban-board');
 if(!board)return;
 let dragged=null;
 board.querySelectorAll('.kanban-card').forEach(card=>{
  card.addEventListener('dragstart',()=>{dragged=card;card.classList.add('dragging')});
  card.addEventListener('dragend',()=>card.classList.remove('dragging'));
 });
 board.querySelectorAll('.kanban-dropzone').forEach(zone=>{
  zone.addEventListener('dragover',e=>{e.preventDefault();zone.classList.add('drag-over')});
  zone.addEventListener('dragleave',()=>zone.classList.remove('drag-over'));
  zone.addEventListener('drop',async e=>{
   e.preventDefault();zone.classList.remove('drag-over');
   if(!dragged)return;
   const column=zone.closest('.kanban-column');
   const status=column.dataset.status;
   const original=dragged.parentElement;
   zone.appendChild(dragged);
   const body=new URLSearchParams({id:dragged.dataset.id,status,csrf_token:board.dataset.csrf});
   try{
    const res=await fetch('tasks/update-status.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
    const data=await res.json();
    if(!res.ok||!data.ok)throw new Error(data.message||'Update failed');
    updateCounts();
   }catch(err){original.appendChild(dragged);alert(err.message);}
  });
 });
 function updateCounts(){
  board.querySelectorAll('.kanban-column').forEach(col=>{
   col.querySelector('.kanban-head span').textContent=col.querySelectorAll('.kanban-card').length;
  });
 }
})();
