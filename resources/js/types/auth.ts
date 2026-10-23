export type User = {
    id: number;

    // Personal information
    name: string;
    last_name: string;
    email: string;
    tel_num: string | null;
    address: string | null;
    about: string | null;

    // Uploaded file paths
    image: string | null;
    cv: string | null;

    // Existing properties
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;

    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
