/*
cp libdatasafe.so /usr/lib
gcc -ldatasafe -L/usr/lib test_shopex_data_encrypt.c -o test_shopex_data_encrypt 

Written by Ken Xu (Kyle<xuqinyong@gmail.com>)
Copyright (C) 2010, ShopEx. 
All rights reserved.
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <openssl/rsa.h>
#include <openssl/pem.h>
#include "datasafe_api.h"

main(){	
	
	char *source_filename = "/etc/shopex/skomart.com/sec.pem.en";
	
	char *file_content = NULL;
	int file_content_len = 0;
	
	FILE *fp;

	char *output;
	int output_len;
	int de_len;
	
	int i = 0;
	
	RSA *priv_rsa;
	
	if((fp=fopen(source_filename,"rb"))==NULL)
	{
		printf("cant open the file");
		exit(0);
	}
	
	fseek(fp, 0L, SEEK_END);
	file_content_len = ftell(fp);
	fseek(fp, 0L, SEEK_SET);
	file_content = (char *)malloc(file_content_len);
	fread(file_content, 1, file_content_len, fp );
	file_content[file_content_len] = '\0';
	fclose(fp);
	
	shopex_conf_rsa_decrypt(file_content,file_content_len,&output,&output_len);
	
	printf("%d\n",output_len);
	for(i=0;i<output_len;i++){
		printf("%2x",output[i]);
	}
	priv_rsa=d2i_RSAPrivateKey(NULL,(const unsigned char**)&output,(long)de_len);
		
	RSA_print_fp(stdout,priv_rsa,11);
	
	RSA_free(priv_rsa);

}