<?php
/**
 * @version 1.0
 * RSA AES 解密类
 * @copyright denglu1 tech
 */
class EncryptUtil {

    const PUBLICKKEY = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmnbylxC9UuUQzZGJ9k63
aFdE65wmr337KeusYFSM4c8qX5U6rg3AAAoGCtMAm8U6MoYFFAN4vbReJL8Nm68D
gfmSzP6UoF+y++4k6+2LvNbzIuQ9z9MdjWL+xMLLFvjtn38HdvaRJ3P7ClJn3UPs
zdueJH0A8dsfWXF0bgGqlWaW/gnSdP38w9/o9hRoJTUeqOq41Y/hwn+FY3axhWCl
vCaFNBvguBRAEjRz0/imJYRnCP0uMRlnB8PVwtyWHNQ2Ok7J8ujvg0UWloC5mMDz
XQsBPfMDo35HCzUEyDVHMvTIVFLQixNl3HRvc7I+Yj4CDyDpJ0E5iH/DaXcw+WzL
DQIDAQAB
-----END PUBLIC KEY-----
";

    const PRIVATEKEY = "-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCadvKXEL1S5RDN
kYn2TrdoV0TrnCavffsp66xgVIzhzypflTquDcAACgYK0wCbxToyhgUUA3i9tF4k
vw2brwOB+ZLM/pSgX7L77iTr7Yu81vMi5D3P0x2NYv7EwssW+O2ffwd29pEnc/sK
UmfdQ+zN254kfQDx2x9ZcXRuAaqVZpb+CdJ0/fzD3+j2FGglNR6o6rjVj+HCf4Vj
drGFYKW8JoU0G+C4FEASNHPT+KYlhGcI/S4xGWcHw9XC3JYc1DY6Tsny6O+DRRaW
gLmYwPNdCwE98wOjfkcLNQTINUcy9MhUUtCLE2XcdG9zsj5iPgIPIOknQTmIf8Np
dzD5bMsNAgMBAAECggEAc/i8mMk7rCJJ5F3SPHjLYW6sU02RCg/HZKkKaeTxRZTa
FpufTBEMx7q+4J+dhFanJEnVRDp7C8uBJ4u+NtZHBu4P1xOJGYPgiSA63lwin2yv
bdH5yrCSLpZV+/rYqm3JUwf33cywHPQKVqyaSNl30POEH5cu/2dqeSgip3lezv3l
4zrVuoCmuFtVNvf8jj78OjjnYv8gUWujaVma46xigRty8SvHKIZPbyFMf3SedTpu
YQqnNYRR5G3+N/7NIXbVWO16pOTAH7qxFD76mZciMc5j1y7sjbEi3Idx7jISzOYE
+DmeaEFfhFqT/GVJLBhSaNO8Avetp6fjD45DYhym4QKBgQDHdcvUk28nMq1Bf5si
7Rz0FQ5QNjcNfRSGVHjNmMZFifv9R9lzVNfq/JHRlWE0KajaYqlC7c2tdaKUKhQo
6Eb/SqsQnw4puNWUzOEb7o+yP00OrbcGYjWd5xqWA0+x2UFVfxBvPcnDsSFXvYtH
hsFe4gHFgIl1oIKDJ58IT6Dk9QKBgQDGP/elWuDWojniCeb454Rav57SJQSUcPmy
jsqXGXBM6CsFDjlsxFY8p8qlB3vVuIedrP9eRKf6OfMVek96bIx4ucwmvSfYffeH
KY5j9VzcCGRlFoXmiWaAKobq4HNOPAk2AGRbFIGZsLImWiVVN5k38oXKMnmZkeWg
fVtMi2Q+uQKBgQCeTNoJiz2DYY/p1MmeLYt2GCP0+yI0PVoFxpLdsWtik0F/4f8t
rl9y6yMbsC+mQ430TKtDKBK9oQf4CXFYigiW4n7jwShvandwEi5yiaJX+C9DABGr
KlSdTmZmOpiMcP2OSjsT3nj8K99nkFIy4Tsk/8AiLIJr7YjHaLGp2fC65QKBgBUb
AEFRUHhpIuJmjXlYJGYI2l6i5D7tXBffTcASTDtTyCisn/5tVIT2lANHra5OC9oE
bddaVcu02aUitU1iOv4rQYmqP5CzsGNqUCdiGrWuCwocALtPS2M/o8djh8L/bzeZ
iRWNNFLpVb3xgOH2nlAveIDTKNDpkFmqnsqiWFS5AoGAeR53CzI68ZOJOUpYaBw1
mONQ32xlrc5hhzMHrgPCRDkKl3jOxV85cBkbiOv42M2DsIc1NFC4w5FHIDGEKExE
t/FNCtmXXqpws7yfHwcM8sjWCufu7xr3XNeW7XDzTA06P9OhvGPtFZmvlLnFnR30
4H7nhhxKj3rAtleW11R0Tw8=
-----END PRIVATE KEY-----
";

    /**
     * aes编码
     * @param  string $sPassword 密码
     * @param  string $sData     明文
     * @param  string $sIV       加密向量
     * @param  string $sMethod   加密方式(默认：AES-256-CFB)
     * @return string            密文
     */
    public static function aesEncrypt($sPassword, $sData, $sMethod = "AES-256-CFB8") {
        $sIV = chr(0x16) . chr(0x61) . chr(0x0F) . chr(0x3A) . chr(0x37) . chr(0x3D) . chr(0x1B) . chr(0x51) . chr(0x4A) . chr(0x39) . chr(0x5A) . chr(0x79) . chr(0x29) . chr(0x08) . chr(0x01) . chr(0x22);
        $sPassword = base64_decode($sPassword);
        $sPassword = substr(hash('sha256', $sPassword, true), 0, 32);
        $sEncrypted = base64_encode(openssl_encrypt($sData, $sMethod, $sPassword, OPENSSL_RAW_DATA, $sIV));
        return $sEncrypted;
    }

    /**
     * aes解码
     * @param  string $sPassword 密码
     * @param  string $sData     密文
     * @param  string $sIV       加密向量
     * @param  string $sMethod   加密方式(默认：AES-256-CFB)
     * @return string            明文
     */
    public static function aesDecrypt($sPassword, $sData, $sMethod = "AES-256-CFB8") {
        $sIV = chr(0x16) . chr(0x61) . chr(0x0F) . chr(0x3A) . chr(0x37) . chr(0x3D) . chr(0x1B) . chr(0x51) . chr(0x4A) . chr(0x39) . chr(0x5A) . chr(0x79) . chr(0x29) . chr(0x08) . chr(0x01) . chr(0x22);
        $sPassword = base64_decode($sPassword);
        $sDecrypted = openssl_decrypt(base64_decode($sData), $sMethod, $sPassword, OPENSSL_RAW_DATA, $sIV);
        return $sDecrypted;
    }

    /**
     * rsa公钥加密
     * @param  string $sPublicKey 公钥
     * @param  string $sData      明文
     * @return string             密文
     */
    public static function rsaPublicKeyEncrypt($sPublicKey, $sData) {
        $res = openssl_get_publickey($sPublicKey);
        //$sData = base64_encode($sData);
        openssl_public_encrypt($sData, $sEncrypt, $res);
        openssl_free_key($res);
        $sEncrypt = base64_encode($sEncrypt);
        return $sEncrypt;
    }

    /**
     * rsa密钥解密
     * @param  string $sPrivateKey 密钥
     * @param  string $sData       密文
     * @return string              明文
     */
    public static function rsaPrivateKeyDecrypt($sPrivateKey, $sData) {
        $res = openssl_get_privatekey($sPrivateKey);
        $sEncrypt = base64_decode($sData);
        openssl_private_decrypt($sEncrypt, $sDecrypt, $res);
        openssl_free_key($res);
        return $sDecrypt;
    }
    /**
     * @param string $sTextz            明文
     * @param string $sRasEncryptedKey  经过RSA加密后的密文
     * @return string                   明文
     */
    public static function rsa_aes_decrypt($sText, $sRasEncryptedKey) {
        return self::aesDecrypt(self::rsaPrivateKeyDecrypt(self::PRIVATEKEY, $sRasEncryptedKey), $sText);
    }
}