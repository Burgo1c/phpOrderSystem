drop table IF EXISTS m_user;

create table ueda.m_user (
  user_id character varying(50) not null
  , user_nm character varying(50) not null
  , password character varying(100) not null
  , auth_cd character varying(3) not null
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone DEFAULT CURRENT_TIMESTAMP not null
  , primary key (user_id)
);

drop table IF EXISTS m_tokuisaki;

create table ueda.m_tokuisaki (
  tokuisaki_cd character varying(10) not null
  , tokuisaki_nm character varying(40) not null
  , tokuisaki_kana character varying(64)
  , tokuisaki_zip character varying(8) 
  , tokuisaki_adr_1 character varying(15) 
  , tokuisaki_adr_2 character varying(50) 
  , tokuisaki_adr_3 character varying(50)
  , tokuisaki_tel character varying(12) 
  , tokuisaki_fax character varying(12)
  , delivery_kbn character varying(3)
  , delivery_time_kbn character varying(3)
  , delivery_time_hr character varying(2)
  , delivery_time_min character varying(2)
  , delivery_instruct_kbn character varying(3)
  , tanto_nm character varying(20)
  , fuzai_contact character varying(12)
  , industry_cd character varying(3)
  , order_print_kbn character varying(3)
  , delivery_instruct character varying(32)
  , comment character varying(512)
  , bill_dt character varying(3)
  , sale_kbn character varying(3)
  , yamato_kbn character varying(3)
  , jikai_kbn_1 character varying(3)
  , jikai_kbn_2 character varying(3)
  , jikai_kbn_3 character varying(3)
  , search_flg character varying(3)
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone DEFAULT CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) not null 
  , update_date timestamp(6) without time zone DEFAULT CURRENT_TIMESTAMP not null
  , primary key (tokuisaki_cd)
);

drop table IF EXISTS m_tokuisaki_tel;

create table ueda.m_tokuisaki_tel (
  tokuisaki_cd character varying(10) not null
  , tel_no character varying(12) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (tel_no)
);

drop table IF EXISTS m_okurisaki; 

create table ueda.m_okurisaki (
  tokuisaki_cd character varying(10) not null
  , okurisaki_cd character varying(10) not null
  , okurisaki_nm character varying(40) not null
  , okurisaki_kana character varying(64) 
  , okurisaki_zip character varying(7) 
  , okurisaki_adr_1 character varying(15) 
  , okurisaki_adr_2 character varying(50) 
  , okurisaki_adr_3 character varying(50)
  , okurisaki_tel character varying(12) not null
  , okurisaki_fax character varying(12)
  , tanto_nm character varying(40)
  , fuzai_contact character varying(12)
  , delivery_instruct character varying(32)
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (tokuisaki_cd, okurisaki_cd)
);

drop table IF EXISTS m_shohin; 

create table ueda.m_shohin (
  product_cd character varying(3) not null
  , product_nm character varying(40) not null
  , product_nm_abrv character varying(10) not null
  , product_type character varying(3) not null
  , sale_tani character varying(3) not null
  , sale_price character varying(7) not null
  , unit_price character varying(7) not null
  , label_disp_kbn character varying(3) not null
  , order_disp_kbn character varying(3) not null
  , haiban_kbn character varying(3) not null
  , tax_kbn character varying(4) not null
  , sale_kbn character varying(3)
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (product_cd)
);

drop table IF EXISTS m_product_cd; 

create table ueda.m_product_cd (
  product_cd character varying(3) not null
  , in_use boolean not null
  , primary key (product_cd)
);

drop table IF EXISTS m_tokuisaki_shohin; 

create table ueda.m_tokuisaki_shohin (
  tokuisaki_cd character varying(10) not null
  , product_cd character varying(3) not null
  , sale_price character varying(7) not null
  , unit_price character varying(7) not null
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (tokuisaki_cd, product_cd)
);

drop table IF EXISTS m_code; 

create table ueda.m_code (
  kanri_key character varying(50) not null
  , kanri_key_nm character varying(50) not null
  , kanri_cd character varying(50) not null
  , kanri_nm character varying(50) not null
  , entry_user_id character varying(50) default 'SYSTEM' not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) default 'SYSTEM' not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (kanri_key, kanri_cd)
);

drop table IF EXISTS m_zip; 

create table ueda.m_zip (
  zip character varying(10) not null
  , ken_fu character varying(10)
  , shi_ku character varying(20)
  , machi character varying(50)
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (zip)
);

drop table IF EXISTS m_yamato; 

create table ueda.m_yamato (
  record_kbn character varying(1) not null
  , key_part character varying(11) not null
  , delivery_cd character varying(7)
  , mail_cd character varying(7)
  , start_dt character varying(8)
  , kubun character varying(2)
  , yobi character varying(6)
  , create_dt character varying(8)
  , primary key (key_part)
);

drop table IF EXISTS m_printer;

create table ueda.m_printer (
  id character varying(4) not null
  , report_nm character varying(20)
  , printer_nm character varying(50)
  , primary key (id)
);

drop table IF EXISTS m_mail;

create table ueda.m_mail (
  mail character varying(50) not null
  , primary key (mail)
);

drop table IF EXISTS t_memo; 

create table ueda.t_memo (
  id character varying(3) not null
  , memo character varying(1000)
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (id)
);

drop table IF EXISTS t_sale_h; 

create table ueda.t_sale_h (
  order_no character varying(10) not null
  , next_kbn character varying(3)
  , sale_dt timestamp(6) without time zone not null
  , tokuisaki_cd character varying(10) not null
  , okurisaki_cd character varying(10)
  , inquire_no character varying(12) not null
  , order_kbn character varying(3) not null
  , sale_kbn character varying(3) not null
  , delivery_kbn character varying(3) not null
  , receive_dt timestamp(6) without time zone not null
  , delivery_time_kbn character varying(3)
  , delivery_time_hr character varying(2)
  , delivery_time_min character varying(2)
  , delivery_instruct_kbn character varying(3)
  , total_qty character varying(9) not null
  , total_cost character varying(9) not null
  , tax_8 character varying(9) not null
  , tax_10 character varying(9) not null
  , grand_total character varying(9) not null
  , kosu integer default 0 not null
  , sender_cd character varying(3)
  , delivery_form_flg character varying(1) default '0'
  , shuka_report_flg character varying(1) default '0'
  , shuka_print_qty integer default 0
  , shuka_print_dt character varying(10)
  , yamato_kbn character varying(3)
  , send_flg character varying(3)
  , send_dt timestamp(6) without time zone
  , kenpin_kbn character varying(3) default '0'
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (order_no)
);

drop table IF EXISTS t_sale_d; 

create table ueda.t_sale_d (
  order_no character varying(10) not null
  , row_no character varying(3) not null
  , product_cd character varying(3) not null
  , product_nm character varying(25)
  , tanka character varying(9) not null
  , qty character varying(9) not null
  , total_cost character varying(9) not null
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (order_no, row_no)
);

drop table IF EXISTS t_sale_report; 

create table ueda.t_sale_report (
  order_no character varying(10) not null
  , denpyo_flg character varying(1) not null
  , hikae_flg character varying(1) not null
  , receipt_flg character varying(1) not null
  , order_flg character varying(1) not null
  , label_flg character varying(1) not null
  , print_flg character varying(1) not null
  , entry_user_id character varying(50) not null
  , entry_date timestamp(6) without time zone not null
  , update_user_id character varying(50) not null
  , update_date timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (order_no)
);

drop table IF EXISTS t_shuka_log;

create table ueda.t_shuka_log (
  shuka_dt timestamp(6) without time zone not null
  , kensu character varying(7) not null
  , kosu character varying(7) not null
  , send_dt timestamp(6) without time zone default CURRENT_TIMESTAMP not null
  , primary key (shuka_dt)
);


CREATE SEQUENCE seq_order_no INCREMENT BY 1 START WITH 1;
CREATE SEQUENCE seq_inquire_no
    INCREMENT BY 1
    START WITH 1
    MAXVALUE 999999999999
    CYCLE;
CREATE SEQUENCE seq_tokuisaki_cd INCREMENT BY 1 START WITH 1;

CREATE INDEX kenpin_index ON t_sale_h (sale_dt);
CREATE INDEX sale_h_index ON t_sale_h (sale_dt DESC, order_no DESC);

INSERT INTO m_product_cd (product_cd, in_use)
SELECT LPAD(generate_series(0, 999)::text, 3, '0'), FALSE;

UPDATE m_product_cd
SET in_use = TRUE
WHERE product_cd IN (
  SELECT DISTINCT product_cd
  FROM m_shohin
);
