import React from 'react';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import discord from '@/api/auth/discord';
import { faAt, faCommentDots } from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';

const WelcomeContainer = () => {
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const onSubmit = () => {
        clearFlashes();

        discord()
            .catch(error => {
                console.error(error);

                clearAndAddHttpError({ error });
            });
    };

    return (
        <LoginFormContainer title={'Welcome to Nero!'} css={tw`w-full flex`}>
            <div css={tw`mt-6`}>
                <Button size={'xlarge'} onSubmit={onSubmit}>
                    <FontAwesomeIcon icon={faCommentDots}/> Login with Discord
                </Button>
            </div>
            <div css={tw`mt-6 mb-6`}>
                <Link to={'/auth/login/email'}>
                    <Button size={'xlarge'}>
                        <FontAwesomeIcon icon={faAt}/> Login with Email
                    </Button>
                </Link>
            </div>
        </LoginFormContainer>
    );
};

export default WelcomeContainer;
