 
 \ d S 
 s e l e c t   c o n n a m e   f r o m   p g _ c o n s t r a i n t   w h e r e   c o n n a m e   l i k e   ' % f i r m a % ' ; 
 
 c r e a t e   t a b l e   f i r m a _ f i l i a   ( 
         i d   s e r i a l   p r i m a r y   k e y , 
         n a z w a   t e x t 
         - - - ? ? ? 
         
 ) ; 
 
 - - d e n o r m a l i z e   ? ? ? ? 
 c r e a t e   t a b l e   k o d y _ r e j e s t r a c j a _ f i l i a   ( 
         k o d   v a r c h a r ( 6 )   p r i m a r y   k e y , 
         i d _ f i r m a _ f i l i a   i n t e g e r   r e f e r e n c e s   f i r m a _ f i l i a ( i d )   n o t   n u l l 
 ) ; 
 
 - -   a l t e r   t a b l e   k o d y _ r e j e s t r a c j a _ f i l i a   a d d   c o l u m n   i d   s e r i a l   p r i m a r y   k e y ; 
 
 c r e a t e   v i e w   k o d _ f i l i a   a s   s e l e c t   f i r m a _ f i l i a . n a z w a ,   k o d y _ r e j e s t r a c j a _ f i l i a . k o d   
 f r o m   f i r m a _ f i l i a   j o i n   k o d y _ r e j e s t r a c j a _ f i l i a   o n   f i r m a _ f i l i a . i d   =   k o d y _ r e j e s t r a c j a _ f i l i a . i d _ f i r m a _ f i l i a ; 
 
 i n s e r t   i n t o   f i r m a _ f i l i a   ( n a z w a )   v a l u e s   ( ' O l e s n o ' ) ; 
 i n s e r t   i n t o   f i r m a _ f i l i a   ( n a z w a )   v a l u e s   ( ' R a c i b � r z ' ) ; 
 i n s e r t   i n t o   f i r m a _ f i l i a   ( n a z w a )   v a l u e s   ( ' O p o l e ' ) ; 
 i n s e r t   i n t o   f i r m a _ f i l i a   ( n a z w a )   v a l u e s   ( ' G l i w i c e ' ) ; 
 
 
 - - - i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( i d _ f i r m a _ f i l i a ,   k o d )   s e l e c t   ( s e l e c t   i d   f r o m   f i r m a _ f i l i a   w h e r e   n a z w a   =   ' O l e s n o ' ) ,   k o d   f r o m   k o d _ p o c z t o w y   w h e r e   ; 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( i d _ f i r m a _ f i l i a ,   k o d )   s e l e c t   ( s e l e c t   i d   f r o m   f i r m a _ f i l i a   w h e r e   n a z w a   =   ' O l e s n o ' ) ,   k o d   f r o m   k o d _ p o c z t o w y   w h e r e   k o d   l i k e   ' 4 6 - 2 % '   o r   k o d   l i k e   ' 4 6 - 3 % '   o r   k o d   l i k e   ' 4 6 - 0 4 8 '   o r   k o d   l i k e   ' 4 2 - 2 % '   o r   k o d   l i k e   ' 4 2 - 7 % '   o r   k o d   l i k e   ' 4 2 - 1 % '   o r   k o d   l i k e   ' 4 2 - 3 % '   o r   k o d   l i k e   ' 6 3 % '   o r   k o d   l i k e   ' 9 8 % ' ; 
 
 - - 4 7 - 3 % ,   4 7 - 1 % ,   4 5 % ,   4 6 - 0 0 % ,   4 6 - 0 1 % ,   4 6 - 0 2 % ,   4 6 - 0 3 % ,   4 6 - 0 4 0 ,   4 6 - 0 4 1 ,   4 6 - 0 4 2 ,   4 6 - 0 4 3 ,   4 6 - 0 4 4 ,   4 6 - 0 4 5 ,   4 6 - 0 4 6 ,   4 6 - 0 4 7 ,   4 6 - 0 4 9 ,   4 6 - 0 5 % ,   4 6 - 0 6 %   4 6 - 0 7 % ,   4 6 - 0 8 %   , 4 6 - 0 9 % ,   4 6 - 1 % ,   4 9 % ,   5 % ,   6 7 % ,   6 8 % ,   4 7 - 2 0 % ,   4 7 - 2 1 % ,   4 7 - 2 2 % ,   4 7 - 2 3 % 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( i d _ f i r m a _ f i l i a ,   k o d )   s e l e c t   ( s e l e c t   i d   f r o m   f i r m a _ f i l i a   w h e r e   n a z w a   =   ' O p o l e ' ) ,   k o d   f r o m   k o d _ p o c z t o w y   w h e r e   k o d   l i k e   ' 4 8 - 2 6 % '   o r   k o d   l i k e   ' 4 8 - 3 % '   o r   k o d   l i k e   ' 4 7 - 3 % '   o r   k o d   l i k e   ' 4 7 - 1 % '   o r   k o d   l i k e   ' 4 5 % '   o r   k o d   l i k e   ' 4 6 - 0 0 % '   o r   k o d   l i k e   ' 4 6 - 0 1 % '   o r   k o d   l i k e   ' 4 6 - 0 2 % '   o r   k o d   l i k e   ' 4 6 - 0 3 % '   o r   k o d   l i k e   ' 4 6 - 0 4 0 '   o r   k o d   l i k e   ' 4 6 - 0 4 1 '   o r   k o d   l i k e   ' 4 6 - 0 4 2 '   o r   k o d   l i k e   ' 4 6 - 0 4 3 '   o r   k o d   l i k e   ' 4 6 - 0 4 4 '   o r   k o d   l i k e   ' 4 6 - 0 4 5 '   o r   k o d   l i k e   ' 4 6 - 0 4 6 '   o r   k o d   l i k e   ' 4 6 - 0 4 7 '   o r   k o d   l i k e   ' 4 6 - 0 4 9 '   o r   k o d   l i k e   ' 4 6 - 0 5 % '   o r   k o d   l i k e   ' 4 6 - 0 6 % '   o r   k o d   l i k e   ' 4 6 - 0 7 % '   o r   k o d   l i k e   ' 4 6 - 0 8 % '   o r   k o d   l i k e   ' 4 6 - 0 9 % '   o r   k o d   l i k e   ' 4 6 - 1 % '   o r   k o d   l i k e   ' 4 9 % '   o r   k o d   l i k e   ' 5 % '   o r   k o d   l i k e   ' 6 7 % '   o r   k o d   l i k e   ' 6 8 % '   o r   k o d   l i k e   ' 4 7 - 2 0 % '   o r   k o d   l i k e   ' 4 7 - 2 1 % '   o r   k o d   l i k e   ' 4 7 - 2 2 % '   o r   k o d   l i k e   ' 4 7 - 2 3 % ' ; 
 
 - - 4 7 - 2 4 % ,   4 7 - 2 5 % ,   4 7 - 2 6 % ,   4 7 - 2 7 % ,   4 7 - 2 8 % ,   4 7 - 2 9 % ,   4 8 - 1 % ,   4 8 - 2 0 % ,   4 8 - 2 1 % ,   4 8 - 2 2 % ,   4 8 - 2 3 % ,   4 8 - 2 4 % ,   4 8 - 2 5 % ,   4 3 - 2 0 % ,   4 3 - 2 1 % ,   4 3 - 2 2 1 ,   4 3 - 2 2 2 ,   4 3 - 2 2 3 ,   4 3 - 2 2 4 ,   4 3 - 2 2 5 ,   4 3 - 2 2 6 ,   4 3 - 2 2 7 ,   4 3 - 2 2 8 ,   4 3 - 2 2 9 ,   4 3 - 2 3 % ,   4 3 - 2 4 % ,   4 3 - 2 5 % ,   4 3 - 2 6 % ,   4 3 - 2 7 % ,   4 3 - 2 8 % ,   4 3 - 2 9 % ,   4 3 - 3 % ,   4 3 - 4 % ,   4 3 - 5 % ,   4 3 - 7 % ,   4 3 - 8 % ,   4 3 - 9 % ,   4 3 - 1 7 % ,   4 3 - 1 8 % ,   4 3 - 1 9 % ,   3 4 - 3 % ,   4 7 - 4 % ,   4 4 - 2 % ,   4 4 - 3 % 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( i d _ f i r m a _ f i l i a ,   k o d )   s e l e c t   ( s e l e c t   i d   f r o m   f i r m a _ f i l i a   w h e r e   n a z w a   =   ' R a c i b � r z ' ) ,   k o d   f r o m   k o d _ p o c z t o w y   w h e r e   k o d   l i k e   ' 4 7 - 2 4 % '   o r   k o d   l i k e   ' 4 7 - 2 5 % '   o r   k o d   l i k e   ' 4 7 - 2 6 % '   o r   k o d   l i k e   ' 4 7 - 2 7 % '   o r   k o d   l i k e   ' 4 7 - 2 8 % '   o r   k o d   l i k e   ' 4 7 - 2 9 % '   o r   k o d   l i k e   ' 4 8 - 1 % '   o r   k o d   l i k e   ' 4 8 - 2 0 % '   o r   k o d   l i k e   ' 4 8 - 2 1 % '   o r   k o d   l i k e   ' 4 8 - 2 2 % '   o r   k o d   l i k e   ' 4 8 - 2 3 % '   o r   k o d   l i k e   ' 4 8 - 2 4 % '   o r   k o d   l i k e   ' 4 8 - 2 5 % '   o r   k o d   l i k e   ' 4 3 - 2 0 % '   o r   k o d   l i k e   ' 4 3 - 2 1 % '   o r   k o d   l i k e   ' 4 3 - 2 2 1 '   o r   k o d   l i k e   ' 4 3 - 2 2 2 '   o r   k o d   l i k e   ' 4 3 - 2 2 3 '   o r   k o d   l i k e   ' 4 3 - 2 2 4 '   o r   k o d   l i k e   ' 4 3 - 2 2 5 '   o r   k o d   l i k e   ' 4 3 - 2 2 6 '   o r   k o d   l i k e   ' 4 3 - 2 2 7 '   o r   k o d   l i k e   ' 4 3 - 2 2 8 '   o r   k o d   l i k e   ' 4 3 - 2 2 9 '   o r   k o d   l i k e   ' 4 3 - 2 3 % '   o r   k o d   l i k e   ' 4 3 - 2 4 % '   o r   k o d   l i k e   ' 4 3 - 2 5 % '   o r   k o d   l i k e   ' 4 3 - 2 6 % '   o r   k o d   l i k e   ' 4 3 - 2 7 % '   o r   k o d   l i k e   ' 4 3 - 2 8 % '   o r   k o d   l i k e   ' 4 3 - 2 9 % '   o r   k o d   l i k e   ' 4 3 - 3 % '   o r   k o d   l i k e   ' 4 3 - 4 % '   o r   k o d   l i k e   ' 4 3 - 5 % '   o r   k o d   l i k e   ' 4 3 - 7 % '   o r   k o d   l i k e   ' 4 3 - 8 % '   o r   k o d   l i k e   ' 4 3 - 9 % '   o r   k o d   l i k e   ' 4 3 - 1 7 % '   o r   k o d   l i k e   ' 4 3 - 1 8 % '   o r   k o d   l i k e   ' 4 3 - 1 9 % '   o r   k o d   l i k e   ' 3 4 - 3 % '   o r   k o d   l i k e   ' 4 7 - 4 % '   o r   k o d   l i k e   ' 4 4 - 2 % '   o r   k o d   l i k e   ' 4 4 - 3 % ' ; 
 
 
 - - 4 3 - 1 0 % ,   4 3 - 1 1 % ,   4 3 - 1 2 % ,   4 3 - 1 3 % ,   4 3 - 1 4 % ,   4 3 - 1 5 % ,   4 3 - 1 6 % ,   4 1 % ,   4 2 - 4 % ,   4 2 - 5 % ,   4 2 - 6 % ,   4 3 - 2 2 0 ,   4 3 - 6 % ,   4 4 - 1 % ,   4 0 % ,   3 2 % ,   3 3 % ,   3 4 - 0 % ,   3 4 - 1 % ,   3 4 - 2 % ,   3 4 - 4 % ,   3 4 - 5 % ,   3 4 - 6 % ,   3 4 - 7 % ,   3 4 - 8 % ,   3 4 - 9 % 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( i d _ f i r m a _ f i l i a ,   k o d )   s e l e c t   ( s e l e c t   i d   f r o m   f i r m a _ f i l i a   w h e r e   n a z w a   =   ' G l i w i c e ' ) ,   k o d   f r o m   k o d _ p o c z t o w y   w h e r e   k o d   l i k e   ' 4 3 - 1 0 % '   o r   k o d   l i k e   ' 4 3 - 1 1 % '   o r   k o d   l i k e   ' 4 3 - 1 2 % '   o r   k o d   l i k e   ' 4 3 - 1 3 % '   o r   k o d   l i k e   ' 4 3 - 1 4 % '   o r   k o d   l i k e   ' 4 3 - 1 5 % '   o r   k o d   l i k e   ' 4 3 - 1 6 % '   o r   k o d   l i k e   ' 4 1 % '   o r   k o d   l i k e   ' 4 2 - 4 % '   o r   k o d   l i k e   ' 4 2 - 5 % '   o r   k o d   l i k e   ' 4 2 - 6 % '   o r   k o d   l i k e   ' 4 3 - 2 2 0 '   o r   k o d   l i k e   ' 4 3 - 6 % '   o r   k o d   l i k e   ' 4 4 - 1 % '   o r   k o d   l i k e   ' 4 0 % '   o r   k o d   l i k e   ' 3 2 % '   o r   k o d   l i k e   ' 3 3 % '   o r   k o d   l i k e   ' 3 4 - 0 % '   o r   k o d   l i k e   ' 3 4 - 1 % '   o r   k o d   l i k e   ' 3 4 - 2 % '   o r   k o d   l i k e   ' 3 4 - 4 % '   o r   k o d   l i k e   ' 3 4 - 5 % '   o r   k o d   l i k e   ' 3 4 - 6 % '   o r   k o d   l i k e   ' 3 4 - 7 % '   o r   k o d   l i k e   ' 3 4 - 8 % '   o r   k o d   l i k e   ' 3 4 - 9 % ' ; 
 
 
 - - - t e s t   i n s e r t s ,   d o   n o t   u s e   i t   a f t e r   a l l 
 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( k o d ,   i d _ f i r m a _ f i l i a )   v a l u e s   ( ' 4 6 - 2 2 0 ' ,   1 ) ; 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( k o d ,   i d _ f i r m a _ f i l i a )   v a l u e s   ( ' 4 6 - 3 1 0 ' ,   1 ) ; 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( k o d ,   i d _ f i r m a _ f i l i a )   v a l u e s   ( ' 4 7 - 2 2 4 ' ,   2 ) ; 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( k o d ,   i d _ f i r m a _ f i l i a )   v a l u e s   ( ' 4 4 - 2 1 7 ' ,   2 ) ; 
 i n s e r t   i n t o   k o d y _ r e j e s t r a c j a _ f i l i a   ( k o d ,   i d _ f i r m a _ f i l i a )   v a l u e s   ( ' 4 5 - 7 5 8 ' ,   3 ) ; 
 
 
 
 a l t e r   t a b l e   d a n e _ i n t e r n e t   a d d   c o l u m n   s o u r c e   i n t e g e r   n o t   n u l l   d e f a u l t   1 ;   - - d o m y s l n i e   z w y k l y   f o r m u l a r z   e e n a 
 
 
 d r o p   v i e w   o s o b a _ i n t e r n e t ; 
 
 c r e a t e   o r   r e p l a c e   v i e w   o s o b a _ i n t e r n e t   a s   S E L E C T   d _ o . i d ,   d _ o . i d _ i m i e ,   i m i o n a . n a z w a   A S   i m i e ,   d _ o . n a z w i s k o ,   p l e c . n a z w a   A S   p l e c ,   d _ o . d a t a _ u r o d z e n i a ,   
 m _ z a m . n a z w a   A S   m s c _ z a m ,   d _ o . u l i c a ,   d _ o . k o d ,   w y k s z t a l c e n i e . n a z w a   A S   w y k s z t a l c e n i e ,   z a w o d . n a z w a   A S   z a w o d ,   d _ o . d a t a _ z g l o s z e n i a ,   c h a r a k t e r . n a z w a   A S   c h a r a k t e r ,   
 d _ o . d a t a ,   d _ o . i l o s c _ t y g ,   z r o d l o . n a z w a   A S   z r o d l o ,   d _ o . s o u r c e 
       F R O M   d a n e _ i n t e r n e t   d _ o 
       J O I N   i m i o n a   O N   i m i o n a . i d   =   d _ o . i d _ i m i e 
       J O I N   p l e c   O N   p l e c . i d   =   d _ o . i d _ p l e c 
       J O I N   m i e j s c o w o s c   m _ z a m   O N   m _ z a m . i d   =   d _ o . i d _ m i e j s c o w o s c 
       J O I N   w y k s z t a l c e n i e   O N   w y k s z t a l c e n i e . i d   =   d _ o . i d _ w y k s z t a l c e n i e 
       J O I N   z a w o d   O N   z a w o d . i d   =   d _ o . i d _ z a w o d 
       J O I N   c h a r a k t e r   O N   c h a r a k t e r . i d   =   d _ o . i d _ c h a r a k t e r 
       J O I N   z r o d l o   O N   z r o d l o . i d   =   d _ o . i d _ z r o d l o ; 
       
       
 a l t e r   t a b l e   r e k l a m a c j e   a d d   c o l u m n   i d _ r e k l a m a c j e   s e r i a l   p r i m a r y   k e y ; 
 a l t e r   t a b l e   b a n k   a d d   c o l u m n   s w i f t   v a r c h a r ( 1 1 ) ; 
 
 - -   s e c t i o n   s w i f t   b a n k   u p d a t e s 
 u p d a t e   b a n k   s e t   s w i f t   =   ' A L B P P L P W '   w h e r e   i d   =   3 7 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' A L L B P L P W '   w h e r e   i d   =   4 2 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' E B O S P L P W '   w h e r e   i d   =   3 5 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' P O C Z P L P 4 '   w h e r e   i d   =   3 0 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' P O L U P L P R '   w h e r e   i d   =   3 4 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' P O L U P L P R '   w h e r e   i d   =   3 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' W B K P P L P P '   w h e r e   i d   =   4 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' G O P Z P L P W '   w h e r e   i d   =   2 2 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' B P H K P L P K '   w h e r e   i d   =   5 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' B R E X P L P W '   w h e r e   i d   =   3 9 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' C I T I P L P X '   w h e r e   i d   =   6 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' D E U T P L P K '   w h e r e   i d   =   7 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' M H B F P L P W '   w h e r e   i d   =   3 2 ;   - -   3 8   t o   r e m o v e 
 u p d a t e   b a n k   s e t   s w i f t   =   ' D R E S P L P W '   w h e r e   i d   =   4 3 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' E F G B P L P W '   w h e r e   i d   =   4 0 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' P P A B P L P K '   w h e r e   i d   =   8 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' G B W C P L P P '   w h e r e   i d   =   2 7 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' G B W C P L P P '   w h e r e   i d   =   4 4 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' G B G C P L P K '   w h e r e   i d   =   2 3 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' I N G B P L P W '   w h e r e   i d   =   1 2 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' I V S E P L P P '   w h e r e   i d   =   3 1 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' K R D B P L P W '   w h e r e   i d   =   2 4 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' L U B W P L P R '   w h e r e   i d   =   3 3 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' B R E X P L P W M U L '   w h e r e   i d   =   2 1 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' B I G B P L P W '   w h e r e   i d   =   1 8 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' B R E X P L P W M U '   w h e r e   i d   =   2 6 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' N D E A P L P 2 '   w h e r e   i d   =   4 1 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' P K O P P L P W '   w h e r e   i d   =   2 5 ;     - -   2 0   o u t 
 u p d a t e   b a n k   s e t   s w i f t   =   ' B P K O P L P W '   w h e r e   i d   =   2 9 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' E F G B P L P W '   w h e r e   i d   =   3 6 ;   
 u p d a t e   b a n k   s e t   s w i f t   =   ' R C B W P L P W '   w h e r e   i d   =   1 9 ;   
 
 
 d e l e t e   f r o m   b a n k   w h e r e   i d   =   3 8 ; 
 u p d a t e   d o k u m e n t y   s e t   i d _ b a n k   =   2 5   w h e r e   i d _ b a n k   =   2 0 ; 
 d e l e t e   f r o m   b a n k   w h e r e   i d   =   2 0 ; 
 
 
 - - - - m o d   2 5 . 1 0 . 2 0 1 1   a n d   f u r t h e r 
 d r o p   v i e w   u m o w a   c a s c a d e ; 
 
 C R E A T E   o r   r e p l a c e   V I E W   u m o w a   A S 
         S E L E C T   d a n e _ o s o b o w e . i d ,   d a n e _ o s o b o w e . i m i e ,   d a n e _ o s o b o w e . n a z w i s k o ,   d a n e _ o s o b o w e . i d _ p l e c ,   d a n e _ o s o b o w e . d a t a _ u r o d z e n i a ,   d a n e _ o s o b o w e . u l i c a ,   d a n e _ o s o b o w e . k o d ,   
         m 1 . n a z w a   A S   m s c ,     m 2 . n a z w a   A S   m s c _ u r ,   w y k s z t a l c e n i e . n a z w a   A S   w y k s z t a l c e n i e ,   u p r a w n i e n i a . i m i e _ n a z w i s k o   A S   k o n s u l t a n t ,   z a t r u d n i e n i e . i d   a s   i d _ z a t r u d n i e n i e ,   
         z a t r u d n i e n i e . i d _ k l i e n t ,   z a t r u d n i e n i e . i d _ o d d z i a l ,   z a t r u d n i e n i e . d a t a _ w y j a z d u ,   z a t r u d n i e n i e . i l o s c _ t y g ,   z a w o d . n a z w a   A S   s t a n o w i s k o ,   
         ( ( ( a d r e s _ b i u r o . n a z w a ) : : t e x t   | |   ' ,   ' : : t e x t )   | |   ( m s c _ b i u r a . n a z w a ) : : t e x t )   A S   b i u r o ,   0   a s   i d _ p a n s t w o ,   
         z a t r u d n i e n i e . i d _ w a k a t   F R O M     d a n e _ o s o b o w e   
         J O I N   m i e j s c o w o s c   m 1   O N   d a n e _ o s o b o w e . i d _ m i e j s c o w o s c   =   m 1 . i d 
         J O I N   m i e j s c o w o s c   m 2   O N   d a n e _ o s o b o w e . i d _ m i e j s c o w o s c _ u r   =   m 2 . i d 
         J O I N   w y k s z t a l c e n i e   O N   d a n e _ o s o b o w e . i d _ w y k s z t a l c e n i e   =   w y k s z t a l c e n i e . i d 
         J O I N   u p r a w n i e n i a   O N   d a n e _ o s o b o w e . i d _ k o n s u l t a n t   =   u p r a w n i e n i a . i d 
         J O I N   z a t r u d n i e n i e   O N   d a n e _ o s o b o w e . i d   =   z a t r u d n i e n i e . i d _ o s o b a 
         J O I N   m s c _ o d j a z d u   O N   z a t r u d n i e n i e . i d _ m s c _ o d j a z d   =   m s c _ o d j a z d u . i d 
         J O I N   o d d z i a l y _ k l i e n t   O N   z a t r u d n i e n i e . i d _ o d d z i a l   =   o d d z i a l y _ k l i e n t . i d 
         J O I N   a d r e s _ b i u r o   O N   o d d z i a l y _ k l i e n t . a d r e s _ b i u r o   =   a d r e s _ b i u r o . i d 
         J O I N   m i e j s c o w o s c _ b i u r o   O N   o d d z i a l y _ k l i e n t . i d _ b i u r o   =   m i e j s c o w o s c _ b i u r o . i d 
         J O I N   m s c _ b i u r a   O N   m i e j s c o w o s c _ b i u r o . i d _ m s c _ b i u r o   =   m s c _ b i u r a . i d 
         J O I N   z a w o d   O N   o d d z i a l y _ k l i e n t . s t a n o w i s k o   =   z a w o d . i d 
         W H E R E   ( z a t r u d n i e n i e . i d _ s t a t u s   =   5 )   O R D E R   B Y   d a n e _ o s o b o w e . i d ; 
 
 
 C R E A T E   O R   R E P L A C E   F U N C T I O N   p o d a j d a n e u m o w a ( o s o b a _ i d   i n t e g e r )   R E T U R N S   u m o w a 
         A S   $ $ D E C L A R E 
                 r e s u l t   u m o w a ; 
                 i d _ p _ p o s _ c o n s t   i n t e g e r ; 
                 t e s t   r e c o r d ; 
 B E G I N 
                 i d _ p _ p o s _ c o n s t   : =   2 ; 
                 s e l e c t   i n t o   r e s u l t   *   f r o m   u m o w a   w h e r e   i d   =   o s o b a _ i d ; 
                 s e l e c t   i n t o   t e s t   k l i e n t . i d _ p a n s t w o _ p o s ,   k l i e n t . i d ,   k l i e n t . n a z w a   | |   ' ,   '   | |   k l i e n t . a d r e s   a s   a d r e s _ k l i e n t   f r o m   k l i e n t   w h e r e   k l i e n t . i d   =   r e s u l t . i d _ k l i e n t ; 
                 r e s u l t . i d _ p a n s t w o   : =   t e s t . i d _ p a n s t w o _ p o s ; 
                 
                 I F   t e s t . i d _ p a n s t w o _ p o s   =   i d _ p _ p o s _ c o n s t   T H E N 
                                 - - p o d m i a n a   a d r e s u   k l i e n t a   d l a   p o s r e d n i c t w a   z   p o l s k i 
                                 r e s u l t . b i u r o   : =   t e s t . a d r e s _ k l i e n t ; 
                 E L S E 
                                 r e s u l t . b i u r o   : =   ' E & A   L o g i s t i e k   b v ,   '   | |   r e s u l t . b i u r o ; 
                 E N D   I F ; 
 
                 R E T U R N   r e s u l t ; 
 E N D ; 
 $ $   L A N G U A G E   p l p g s q l ;