SISTEM JUAL MOBIL
====================
tediri dari 2 sistem
1. sistem penjual (folder server)
2. sistem pembeli (folder user)
===================

penjelasan sistem penjual
1. penjual bisa crud mobil (tersimpan di database penjual) (nantinya ditampilkan di pembeli menggunakan API rest)
2. penjual bisa merespoons helpdesk (tersimpan di database penjual) (nantinya ditampilkan di sistem pembeli menggunakan API soap)
   
==================

penjelasan sistem pembeli
1. pembeli bisa melihat stock mobil apa saja yang tersedia (melihat dari database penjual) (API rest)
2. pembeli bisa melakukan pembelian (tersimpan di databse pembeli) (API soap)
3. pembeli melakukan pembayaran (tersimpan di database pembeli, tetapi jika mobil sudah di beli stock mobil di database penjual berkurang) (API soap)
4. pembeli bisa melakukan helpdesk (tersimpan di database penjual) (API soap)
