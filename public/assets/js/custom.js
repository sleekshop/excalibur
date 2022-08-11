/*-------Quantity Counter JS Here------------*/
var qtyDecs = document.querySelectorAll(".decrement");
var qtyIncs = document.querySelectorAll(".increment");

qtyDecs.forEach((qtyDec) => {
  qtyDec.addEventListener("click",function(e){
    if(e.target.nextElementSibling.value > 0){
      e.target.nextElementSibling.value--;
    } else {
      // delete the item, etc
    }
  })
})
qtyIncs.forEach((qtyDec) => {
  qtyDec.addEventListener("click",function(e){
    e.target.previousElementSibling.value++;
  })
})
/*-------Quantity Counter JS Here------------*/